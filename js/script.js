$( document ).ready(function() { 
  
    $(".ui-config").click(function(e){
        let obj = {action: 'get_content' 
				   , ui_id:$(this).data("ui_id")
				  , page_id:$(this).data("page_id")
				  , section_id:$(this).data("section_id")}; 
        $.getJSON(PHPVARS.ajaxurl, obj, function(data) {
  console.log(data);
            $("#id").val(data.id);
            $("#title").val(data.title);
            $("#sub_title").val(data.sub_title);
            CKEDITOR.instances.content.setData(data.content);

        
            $.each(data.file , function( index, value ) {
                $('#image_wrapper').prepend('<img id="'+index+'" src="'+value+'" width="100" height="100" />')
              });
            
  });
        
          e.preventDefault();
    })                

}); 


$( document ).ready(function() { 
  
    $(".submit").click(function(e){
        let obj = {action: 'set_content', id: $("#id").val(), filepath: $("#filepath").val(), title: $("#title").val(), sub_title: $("#sub_title").val(), content: CKEDITOR.instances.content.getData() };
        let obj1 = $("#myform").serialize(); 
        $.ajax({
            method: "POST",
            url: PHPVARS.ajaxurl,
            data: obj
          })
            .done(function( msg ) {
                console.log(msg);
            });
            alert('Succesfully Updated');
        
           e.preventDefault();
    })                

});

// function getContent(val){
//     $.ajax({
//         type:"POST",
//         url:"admin-ajax.php",
//         data: 'gymnast='+val,
//         success: function(response){
//             var result = JSON.parse(response);
//             if (result.response == true) {
//                 var data = result.rows;
//                 $("#title").val(data[0].title);
//                 $("#sub_title").val(data[0].sub_title);
//                 $("#parent").val(data[0].parent);
//                 $("#width").val(data[0].width);
//                 $("#height").val(data[0].height);
//                 $("#label").val(data[0].label);
//                 $("#color").val(data[0].color);
//                 $("#url").val(data[0].url);
//                 $("#tags").val(data[0].tags);
//                 $("#file").val(data[0].file);
//                 $("#status").val(data[0].status);
//                 $("#content").val(data[0].content);
//             }else if (result.response == false) {
//                 $('#myform').append('Not found!');
//             }
//         }
//     });
// }