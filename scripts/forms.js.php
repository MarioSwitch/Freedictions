<?php
echo "
<script>
document.addEventListener(\"DOMContentLoaded\", function(){
	document.querySelectorAll(\"form\").forEach(function(form){
		form.addEventListener(\"submit\", function(event){
			if(!form.checkValidity()){
				return;
			}

			if(form.dataset.submitted === \"true\"){
				event.preventDefault();
				return;
			}

			form.dataset.submitted = \"true\";

			const btn = event.submitter;

			if(btn){
				btn.innerText = \"" . getString("general_loading") . "\";
			}
		});
	});
});
</script>
";