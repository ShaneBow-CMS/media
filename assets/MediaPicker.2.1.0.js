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
	var defaults = {
		mode:'replace', // or 'append'
		btn: null,
		verbose:!1
		};

	$('#mediadb').on('click', '.thumbnail .zoom-image', function(e){
		console.log("clicked thumb zoom");
		if (!_instance)
			return UBOW.flashError('MediaPicker>> No current instance');
		var $img = $(this).siblings('img');
		_instance.selected($img);
		});

	function test_me($img) {
		var s = $img.attr('src');
		UBOW.flashMessage("MEDIA PICKER TEST: " + s);
		}

	function MediaPicker(el, options) {	
		var my=this;

		my.$el = $(el);
		my.settings = $.extend({}, defaults, options);
		if (my.settings.verbose) console.log('MediaPicker('+el+')');
		if (my.settings.verbose) UBOW.dump(my.settings,'MediaPicker.settings');
		my.settings = $.extend({}, defaults, options);

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
				UBOW.flashMessage('mids: ' + my.csvIDs());
				$this.closest('.mp-media').remove();
				});

		my.$el.trigger('MediaPicker:inited');
		}

	MediaPicker.prototype.csvIDs=function(){
		var my = this;
		var ids = '';
		my.$el.find('img').each(function(){
//			ids += (ids?',':'') + this.id.substring(4);
			ids += (ids?',':'') + this.getAttribute('mid');
			});
		return ids;
		}

	MediaPicker.prototype.append=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('id');
		if ($('[mid=' + i +']').length) return UBOW.flashError('Media already inserted');
		my.$el.append(
			'<div class="col-lg-3 col-sm-4 col-xs-6 mp-media" style="text-align:center">'
			+ '<div class="thumbnail same-height-always">'
			+ '<div class="thumb ar ar3x2">'
			+ ' <img src="' + s + '" mid="' + i + '" class="img-responsive img-rounded" /></div>'
	+	'<span class="delete">&times;</span>'
			+ '<div class="caption text-center"><h6 class="text-semibold no-margin">'
			+ t + '<small class="display-block">' + c + '</small></h6></div></div></div>')
			.alignElementsSameHeight();
		}

	MediaPicker.prototype.replace=function($img){
		var my = this,
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('id');
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
