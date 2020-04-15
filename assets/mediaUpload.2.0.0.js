$(function() {
	var $form = $('form.dropzone');
	var $gal = $('#mediadb');
	var $pag = $('#mediadb-paging');
	var exts = ['','jpg','png','gif'];

	function thumb(meta) {
		var ext = exts[meta.type_id];
		return '<div class="thumbnail"><div class="thumb ar ar3x2">' +
			'<img src="/uploads/' + meta.id + '.' + ext +
			'" id="mid' + meta.id + '" alt="' + meta.caption +
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
		dictDefaultMessage: 'Drop file to upload <span>or CLICK</span>',
		autoProcessQueue: false,
		init: function() {
			this.on('success', function(file, resp){
				// console.log('upload ' + file.name + ' success: %o', resp.dat);
				dropper.removeFile(file); // dropper.disable(); dropper.enable();
				UBOW.clearForm($form);
				fetch('');
				$form.closest('.modal').modal('hide');
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

	$form.on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
MediaPicker.test_me($('<img mid="27" src="/uploads/somepic.jpg" >'));
//		dropper.processQueue();
		});

	});
