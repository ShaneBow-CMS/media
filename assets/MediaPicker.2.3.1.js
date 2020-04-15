/**
* MediaPicker.2.3.1
* http://www.shanebow.com
*
* Licensed under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
*
* Copyright 2018, ShaneBow
* http://www.shanebow.com
*
* 20190707 rts 2.3.1 added ImageUploader
* 20180331 rts 2.0.0 support multiple instance, append, replace
*
*/
;( function( window ) {
	'use strict';

	var _instance;
	var $gal = $('#mediadb');
	var $pag = $('#mediadb-paging');
	var exts = ['','jpg','png','gif'];
	var defaults = {
		$dlg: $('#dlg-media-picker'),
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
	function uploadErr(file, e){
		file.status = Dropzone.QUEUED;
		console.log('upload ' + file.name + ' error: %o', e);
		if (e.hasOwnProperty('err'))
			UBOW.handleAjaxError(e.err,e.dat,e.msg,$form);
		else
			UBOW.flashError('upload ' + file + ' error: ' + e);
		}

	function uploadSent(file, xhr, formData){
		// Gets triggered when the form is actually being sent.
		// Hide the success button or the complete form.
		UBOW.flashSuccess('sending ' + file);
		}

	function createDropzone($form, url) {
		var dropper;

		$form.on('submit', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if ((dropper.getQueuedFiles().length == 0) && $form.hasClass('update')) {
				NBOW.ajaxForm($(this),'/media/upload_single',{},reloadPage);
				}
			else dropper.processQueue();
			});

		dropper = new Dropzone($form.get(0), {
			url: url,
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
					if (resp.msg == 'updated') return reloadPage();
					else if (resp.err) return UBOW.flashError(resp.msg);
					this.removeFile(file); // dropper.disable(); dropper.enable();
					UBOW.clearForm($form);
					fetch('');
					uploadSuccess(file, resp);
					UBOW.flashSuccess('uploaded '+ file.name);
					});
				this.on('error', uploadErr);
				this.on('sending', uploadSent);
				}
			});
		return dropper;
		}

	createDropzone(defaults.$dlg.find('form'), '/media/upload_single');

	function reloadPage() {
		UBOW.flashSuccess("OK x reloading...");
		location.replace(location.pathname);
		}

	function uploadSuccess(file, resp) {
		var meta = resp.dat;
		console.log('uploadSuccess: ' + file.name + ' meta: %o', meta);
		var $img = $('<img mid="' + meta.id + '"'
			+ 'src="/uploads/' + meta.id + '.' + meta.ext + '"'
			+ 'title="' + meta.title + '"'
			+ 'alt="' + meta.caption + '" >');
		_instance.selected($img);
		}

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
		my.opts = $.extend({}, defaults, options);
		if (my.opts.verbose) console.log('MediaPicker('+el+')');
		if (my.opts.verbose) UBOW.dump(my.opts,'MediaPicker.settings');

		$(function(){
			my._init()
			});
		}

	MediaPicker.prototype._init = function(content) {
		var my=this, s = my.opts;
		my.$btn = s.btn? $(s.btn) : $el;
		my.$btn.on('click', function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			_instance = my;
			my.opts.$dlg.modal('show');
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
		var ids = '';
		this.$el.find('img').each(function(){
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
		my.opts.$dlg.modal('hide');
		}

	MediaPicker.prototype.selected=function($img){
		var my = this;
		if (my.opts.mode == 'replace')
			my.replace($img);
		else
			my.append($img);
//		my.$el.trigger('MediaPicker:change', [val, txt]);
		}

	// simply uploads a file
	// used for ted pix & avatars
	////////////////////////////////
	class ImageUploader {
		constructor($dlg, options) {
			var my=this;
			var $form = $dlg.find('form');
			var defaults = {
				url: '/media/upload_special',
				onSuccess: function(file, resp){}
				};
			my.opts = $.extend({}, defaults, options);

			var dropper = new Dropzone($form.get(0), {
				url: my.opts.url,
				paramName: "userfile", // field name to transfer file
			//	maxFilesize: 1, // MB
				maxFiles: 1,
				acceptedFiles: '.png', // "audio/*,image/*,.psd,.pdf",
				dictDefaultMessage: 'Drop file to upload <span>or CLICK</span>',
				autoProcessQueue: true,
				init: function() {
					this.on('success', function(file, resp){
						this.removeFile(file); // dropper.disable(); dropper.enable();
						if (resp.err) return UBOW.flashError(resp.msg);
						$dlg.modal('hide');
						UBOW.clearForm($form);
						my.opts.onSuccess(file, resp);
						});
					this.on('error', uploadErr);
					this.on('sending', uploadSent);
					}
				});
			}
		}

	window.ImageUploader = ImageUploader; // add to global namespace
	window.MediaPicker = MediaPicker; // add to global namespace
	})( window );
