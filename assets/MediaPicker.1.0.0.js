UBOW.MediaPicker = function(el, options) {
	var my=this;
	var $el = $(el);
	var defaults = {
		url: null,
		verbose:!1
		};

	(function($) {
		$.fn.found = function(myFunction) {this.length && myFunction.call(this)};
		})(jQuery);

	function init() {
		my.settings = $.extend({}, defaults, options);
		if (my.settings.verbose) UBOW.dump(my.settings,'MediaPicker.settings');
		}

	$('#mediadb').on('click', '.thumbnail .zoom-image', function(e){
		var $img = $(this).siblings('img'),
			s = $img.attr('src'),
			t = $img.attr('title'),
			c = $img.attr('alt'),
			i = $img.attr('id');
		if ($('#_' + i).length) return UBOW.flashError('Media already inserted');
		console.log("clicked zoom: " + s);
		$el.append( // '<img src="' + s + '">');
			'<div class="col-lg-3 col-sm-4 col-xs-6" style="text-align:center">'
			+ '<div class="thumbnail same-height-always"><div class="thumb ar ar3x2">'
			+ ' <img src="' + s + '" id="_' + i + '" class="img-responsive img-rounded"'
			+        ' title="Click to insert in article" /></div>'
			+ '<div class="caption text-center"><h6 class="text-semibold no-margin">'
			+ t + '<small class="display-block">' + c + '</small></h6></div></div></div>')
			.alignElementsSameHeight();
		});

	function csvIDs() {
		var ids = '';
		$el.find('img').each(function(){
			ids += (ids?',':'') + this.id.substring(4);
			});
		return ids;
		}

	init();
	return {
		csvIDs: csvIDs
		}
	}


