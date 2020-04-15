/**
* MediaPicker.2.0.0
* http://www.shanebow.com
*
* Licensed under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
*
* Copyright 2018, ShaneBow
* http://www.shanebow.com
* 20180331 rts 2.0.0 support multiple instance, append, replace
*
*/
;( function( window ) {
	'use strict';

	var _instance;
	var $dlg = $('#dlg-media-picker');
	var $form = $('form.dropzone');
	var $gal = $('#mediadb');
	var $pag = $('#mediadb-paging');
	var exts = ['','jpg','png','gif'];
	var defaults = {
		mode:'replace', // or 'append'
		btn: null,
		verbose:!1
		};

	function thumb(meta) {
		var ext = exts[meta.type_id];
		return '<div class="thumbnail"><div class="thumb ar ar3x2">' +
			'<img src="/uploads/' + meta.id + '.' + ext +
			'" mid="' + meta.id + '" alt="' + meta.caption +
			'" title="' + meta.title + '">' +
			'"<span class="zoom-image"><i class="icon-plus22"></i></span></div>' +

			/*** @TODO fix highslide zIndex from modal
				'<a href="/uploads/' + meta.id + '.' + ext + '" class="highslide" onclick="return hs.expand(this)">' +
				meta.title.substring(0,13) + '</a>' +
			***/
				'<i>' + meta.title.substring(0,13) + '</i>' +

			'</div>';
		}

	function fetch(page) {
		$pag.html('<i>loading page ' + page + '...</i>');
		UBOW.ajax('/media/fetch/'+page, {}, function(err,dat,msg) {
			if (err) return UBOW.handleAjaxError(err,dat,msg);
			$gal.empty();
			$.each(dat.imgs,function(i,meta){$gal.append(thumb(meta));});
			$pag.html(dat.page);
			});
		}

	$pag.on('click', 'a', function(e) {
		e.preventDefault();
		fetch($(this).data("ci-pagination-page"));
		});

	fetch('');

	Dropzone.autoDiscover = false;
	var dropper = new Dropzone($form.get(0), {
		url: '/media/upload_single',
		paramName: "userfile", // field name to transfer file
	//	maxFilesize: 1, // MB
		maxFiles: 1,
		clickable:'.dropzone-previews',
		previewsContainer: '.dropzone-previews',
		acceptedFiles: '.jpg', // "audio/*,image/*,.psd,.pdf",
		dictDefaultMessage: 'Drop file to upload <span>or CLICK</span>',
		autoProcessQueue: false,
		init: function() {
			this.on('success', function(file, resp){
				dropper.removeFile(file); // dropper.disable(); dropper.enable();
				UBOW.clearForm($form);
				fetch('');
				uploadSuccess(file, resp);
				UBOW.flashSuccess('uploaded '+ file.name);
				});
			this.on('error', function(file, e){
				file.status = Dropzone.QUEUED;
				console.log('upload ' + file.name + ' error: %o', e);
				if (e.hasOwnProperty('err'))
					UBOW.handleAjaxError(e.err,e.dat,e.msg,$form);
				else
					UBOW.flashError('upload ' + file + ' error: ' + e);
				});
			this.on('sending', function(file, xhr, formData){
				// Gets triggered when the form is actually being sent.
				// Hide the success button or the complete form.
				UBOW.flashSuccess('sending ' + file);
				});
			}
		});

	function uploadSuccess(file, resp) {
		var meta = resp.dat;
		console.log('uploadSuccess: ' + file.name + ' meta: %o', meta);
		var $img = $('<img mid="' + meta.id + '"'
			+ 'src="/uploads/' + meta.id + '.' + meta.ext + '"'
			+ 'title="' + meta.title + '"'
			+ 'alt="' + meta.caption + '" >');
		_instance.selected($img);
		}

	$form.on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		dropper.processQueue();
		});

	$('#mediadb').on('click', '.thumbnail .zoom-image', function(e){
		console.log("clicked thumb zoom");
		if (!_instance)
			return UBOW.flashError('MediaPicker>> No current instance');
		var $img = $(this).siblings('img');
		_instance.selected($img);
		});

	function MediaPicker(el, options) {	
		var my=this;

		my.$el = $(el);
		my.settings = $.extend({}, defaults, options);
		if (my.settings.verbose) console.log('MediaPicker('+el+')');
		if (my.settings.verbose) UBOW.dump(my.settings,'MediaPicker.settings');

		$(function(){
			my._init()
			});
		}

	MediaPicker.prototype._init = function(content) {
		var my=this,
			s = my.settings;
		my.$btn = s.btn? $(s.btn) : $el;
		my.$btn.on('click', function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			_instance = my;
			$dlg.modal('show');
			});
		// delete handler
		if (s.mode != 'replace')
			my.$el.on('click', '.delete', function(e) {
				e.preventDefault();
				$(this).closest('.mp-media').remove();
				});
		my.$el.trigger('MediaPicker:inited');
		}

	MediaPicker.prototype.csvIDs=function(){
		var my = this;
		var ids = '';
		my.$el.find('img').each(function(){
			ids += (ids?',':'') + this.getAttribute('mid');
			});
		return ids;
		}

	MediaPicker.prototype.append=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('mid');
		if (my.$el.find('[mid=' + i +']').length) return UBOW.flashError('Media already inserted');
		my.$el.append(
			'<div class="col-lg-3 col-sm-4 col-xs-6 mp-media" style="text-align:center">'
			+ '<div class="thumbnail same-height-always">'
			+ ' <div class="thumb ar ar3x2">'
			+ '  <img src="' + s + '" mid="' + i + '" class="img-responsive img-rounded" /></div>'
			+ ' <span class="delete">&times;</span>'
			+ ' <div class="caption text-center"><h6 class="text-semibold no-margin">'
			+ t + '<small class="display-block">' + c + '</small></h6></div></div></div>')
			.alignElementsSameHeight();
		}

	MediaPicker.prototype.replace=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('mid');
		my.$el.find('img').attr('src', s).attr('mid', i);
		$dlg.modal('hide');
		}

	MediaPicker.prototype.selected=function($img){
		var my = this;
		if (my.settings.mode == 'replace')
			my.replace($img);
		else
			my.append($img);
//		my.$el.trigger('MediaPicker:change', [val, txt]);
		}

	window.MediaPicker = MediaPicker; // add to global namespace
	})( window );
