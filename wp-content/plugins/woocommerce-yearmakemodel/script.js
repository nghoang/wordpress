jQuery(document).ready(function(){
	jQuery("#treesearch_form select").change(function(){
		jQuery("#treesearch_form").submit();
	});
});

function GetModelDropbox(select,block_id)
{
	var make_id = jQuery(select).val();
	jQuery("#model_container_" + block_id).html("<img src='/images/loading.gif'>");
	jQuery.post("/wp-load.php",{"action":"GetModelDropbox","make_id":make_id,"block_id":block_id},function(data){
			jQuery("#model_container_" + block_id).html(data);
	});
	
	return false;
}

function GetYearList(select,block_id)
{
	var model_id = jQuery(select).val();
	jQuery("#year_container_" + block_id).html("<img src='/images/loading.gif'>");
	jQuery.post("/wp-load.php",{"action":"GetYearList","model_id":model_id,"block_id":block_id},function(data){
			jQuery("#year_container_" + block_id).html(data);
	});
	
	return false;
}

function confirmDelete(){
      var confirmed = confirm("Do you want to delete this item?");
      return confirmed;
}
