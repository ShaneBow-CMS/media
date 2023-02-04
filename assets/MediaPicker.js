/**
* cms/media/ci/assets/MediaPicker.20220101.js
* http://www.shanebow.com
*
* Copyright 2018 - 2020, ShaneBow
* http://www.shanebow.com
*
* 20230204 rts added custom mode
* 20211120 rts added selector opt to handle tables
* 20211023 rts added onChange()
* 20201209 rts added search capability
* 20190707 rts added ImageUploader
* 20180331 rts support multiple instance, append, replace
*
*/
;( function( window ) {
	'use strict';

	var _instance;
	var $formUpload = $('form.dropzone');
	var exts = ['','jpg','png','gif'];
	var defaults = {
		$dlg: $('#dlg-media-picker'),
		mode:'replace', // or 'append' or 'custom'
		btn: null,
		selector: null, // used for $el.on('click', selector, function(){}...
		verbose:!1,
		thumbClasses: 'col-lg-3 col-sm-4 col-xs-6',
		// onChange: ($img) => console.log img deleted
		// onDelete: () => console.log img deleted
		};
	const thumbMarkup = (meta) => `
		<div class="thumbnail">
		 <div class="thumb ar ar3x2">
		  <img src="/uploads/${meta.file}" mid="${meta.id}"
		       alt="${meta.caption}" title="${meta.title}" />
		  <span class="zoom-image"><i class="icon-plus22"></i></span>
		 </div>
		 <i>${meta.title.substring(0,13)}</i>
		</div>`;
	const remoteTab = new UBOW.PagedList('#media-remote', {
		url: '/media/fetch2/',
		per_page: 25,
		container: '<div class="center"></div>',
		markup: thumbMarkup,
		auto_load: true
		});
	const searchTab = new UBOW.PagedList('#media-search', {
		url: '/media/fetch2/',
		per_page: 25,
		container: '<div></div>',
		markup: thumbMarkup,
		auto_load: false
		});

	Dropzone.autoDiscover = false;
	function uploadErr(file, e){
		file.status = Dropzone.QUEUED;
		console.log('upload ' + file.name + ' error: %o', e);
		if (e.hasOwnProperty('err'))
			UBOW.handleAjaxError(e.err,e.dat,e.msg,$formUpload);
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
			acceptedFiles: '.jpg,.png,.gif', // "audio/*,image/*,.psd,.pdf",
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

	createDropzone(defaults.$dlg.find('form.dropzone'), '/media/upload_single');

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

	$('.media-picker').on('click', '.thumbnail .zoom-image', function(e){
		console.log("clicked thumb zoom");
		if (!_instance)
			return UBOW.flashError('MediaPicker>> No current instance');
		var $img = $(this).siblings('img');
		_instance.selected($img);
		});

	$('.media-picker form.search').on('submit', function(e) {
		e.preventDefault();
		const term = $(this).find('input').val();
		UBOW.flashSuccess('seach: ' + term );
		searchTab.opts.extra = {like: term};
		searchTab.show();
		$('.nav a[href="#media-search"]').tab('show');
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
		// allow for click on img or a separate button
		my.$btn = s.btn? $(s.btn) : my.$el;
		my.$btn.on('click', s.selector, function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			// facilitate $el that matches multiple elements
			my.$clicked = (my.$btn != my.$el)? my.$el : $(this);
			_instance = my;
			my.opts.$dlg.modal('show');
			});
		// delete handler
		if (s.mode != 'replace')
			my.$el.on('click', '.delete', function(e) {
				e.preventDefault();
				$(this).closest('.mp-media').remove();
				my.opts.onDelete && my.opts.onDelete();
				});
		my.$el.trigger('MediaPicker:inited');
		}

	MediaPicker.prototype.clearGallery = function() {
		this.$el.empty();
		}

	// @return csv of mids (media ids)
	MediaPicker.prototype.csvIDs=function(){
		var ids = '';
		this.$el.find('img').each(function(){
			ids += (ids?',':'') + this.getAttribute('mid');
			});
		return ids;
		}

	// @return csv of the aspect ratios
	MediaPicker.prototype.csvARs=function(digits){
		var ars = '';
		this.$el.find('img').each(function(){
			ars += (ars?',':'') + (this.naturalWidth / this.naturalHeight).toPrecision(digits);
			});
		return ars;
		}

	// (src, title, caption, mid)
	MediaPicker.prototype.append=function(s, t, c, i){
		const my = this;

		if (my.$el.find('[mid=' + i +']').length) return UBOW.flashError('Media already inserted');
		my.$el.append(`
			<div class="${my.opts.thumbClasses} mp-media" style="text-align:center">
			 <div class="thumbnail same-height-always">
			  <div class="thumb ar ar3x2">
			   <img src="${s}" mid="${i}" class="img-responsive img-rounded" />
			  </div>
			  <span class="delete">&times;</span>
			  <div class="caption text-center">
          <h6 class="text-semibold no-margin">
			    ${t}<small class="display-block">${c}</small>
			   </h6></div></div></div>`)
			.alignElementsSameHeight();
		return my.$el.find(`img[mid=${i}]`);
		}

	// internal to append img
	//  selected via the dialog
	MediaPicker.prototype._append=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('mid');
		return this.append(s,t,c,i);
		}

	MediaPicker.prototype.replace=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('mid');
	//	my.$el.find('img').attr('src', s).attr('mid', i);
		my.$clicked.find('img').attr('src', s).attr('mid', i);
		my.opts.$dlg.modal('hide');
		}

	MediaPicker.prototype.selected=function($img){
		var my = this;
	//	if (my.opts.onSelect) return my.opts.onSelect($img);
		if (my.opts.mode == 'replace')
			my.replace($img);
		else if (my.opts.mode == 'custom') {
			$img = $img.clone(); // copy image
			my.opts.$dlg.modal('hide'); // close then onChange below
			}
		else
			$img = my._append($img);
		my.opts.onChange && my.opts.onChange($img, my.$clicked);
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
				onSuccess: function(file, resp){},
				acceptedFiles: '.png', // "audio/*,image/*,.psd,.pdf",
				};
			my.opts = $.extend({}, defaults, options);

			var dropper = new Dropzone($form.get(0), {
				url: my.opts.url,
				paramName: "userfile", // field name to transfer file
			//	maxFilesize: 1, // MB
				maxFiles: 1,
				acceptedFiles: my.opts.acceptedFiles, // "audio/*,image/*,.psd,.pdf",
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
