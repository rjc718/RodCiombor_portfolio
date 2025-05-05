<script src="portal/tinymce/js/tinymce/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var targText = document.getElementById('article_text');
	var editorBtn = document.getElementById('article_editor_btn');
	var selector = '#article_text';

	if(!elemExists(targText) && !elemExists(editorBtn)){
		var targText = document.getElementById('module_text');
		var editorBtn = document.getElementById('module_editor_btn');
		var selector = '#module_text';
	}

	editorBtn.addEventListener('click', function() {
		//Replace line breaks if they paste in from Word document
		var text = targText.innerHTML;
		text = text.replace(new RegExp('\r?\n','g'), '<br />');
		targText.innerHTML = text;

		tinymce.init({
			selector: selector,
			content_style: "body#tinymce{padding: 25px; font-size: 20px; line-height: 37px; box-sizing: border-box;} p{margin: 0 0 1em 0;} a{cursor: pointer;} table{border: 1px solid #61666;} li{list-style-position: inside;}",
			content_css : "../assets/css/main2.css",
			remove_script_host : false,
			relative_urls: true,
			height: 450,
			width:1050,
			forced_root_block: 'p',
			fontsize_formats: "10pt 12pt 14pt 16pt 18pt 20pt 24pt 28px 32pt 48pt",
			plugins: [
				'advlist autolink lists link image charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table paste hr wordcount'
			],
			toolbar: 'undo redo | insert | styleselect | bold italic | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code ',
			menubar: false,
			protect: [
				/<\?php.*?\?>/g  // Protect php code
		  	]
		}); //End init
	});
});
</script>