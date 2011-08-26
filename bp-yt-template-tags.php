<?php
class BP_YT_Template{

    var $user_id;
    var $current_item=-1;
    var $item_count;
    var $items;
    var $item;

    var $in_the_loop;
    var $pag_page;
    var $pag_num;
    var $pag_links;
    var $total_item_count;

    
    var $count;
    //for feed
    var $title;
    var $description;
    var $url;
    var $image;
    var $guid;


 function  BP_YT_Template($user_feed_link,$page=null, $per_page=null,$max=null){

     $this->pag_page = isset( $_REQUEST['frpage'] ) ? intval( $_REQUEST['frpage'] ) : $page;
     $this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

       $resp=wp_remote_get($user_feed_link);
        if(!is_wp_error($resp)){
                 $resp=wp_remote_retrieve_body($resp);   //
               $yt_info=maybe_unserialize($resp);
               $yt_info=json_decode($yt_info);
                
               
                 $this->items=$yt_info->feed->entry;//array of items
                 $this->total_item_count=count($this->items);
                  //handle pseudo pagination in the feed
                $this->items= yt_obj_to_array_mapper($this->items);
                if(!empty($this->items))
                 $this->items=array_slice($this->items,($this->pag_page-1)*$this->pag_num,$this->pag_num);
                $this->url=$yt_info->feed->link[1]->href;
               $this->title=$yt_info->feed->title->{'$t'};


                 if ( $max ) {
                     if ( $max >= count($this->items) )
                            $this->items_count = count($this->items);
                    else
                            $this->items_count = (int)$max;
                } else
                    $this->items_count = count($this->items);

             //handle pseudo pagination in the feed

                $this->pag_links = paginate_links( array(
                                'base' => add_query_arg( array( 'frpage' => '%#%', 'num' => $this->pag_num, 's' => $_REQUEST['s'] ) ),
                                'format' => '',
                                'total' => ceil($this->total_item_count / $this->pag_num),
                                'current' => $this->pag_page,
                                'prev_text' => '&larr;',
                                'next_text' => '&rarr;',
                                'mid_size' => 1
                        ));
    }
    else
        $this->items_count=0;///if error, let us not get inside the loop, make has_items false
 }

    function has_items(){
        if($this->items_count)
                return true;
        return false;
    }

    function next_item() {
                    $this->current_item++;
                    $this->item = $this->items[$this->current_item];

                    return $this->item;
            }

    function rewind_items() {
		$this->current_item = -1;
		if ( $this->items_count > 0 ) {
			$this->item = $this->items[0];
		}
	}

    function items() {
		if ( $this->current_item + 1 < $this->items_count ) {
			return true;
		} elseif ( $this->current_item + 1 == $this->items_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_items();
		}

		$this->in_the_loop = false;
		return false;
	}

    function the_item() {
            $this->in_the_loop = true;
            $this->item = $this->next_item();

            if ( 0 == $this->current_item ) // loop has just started
			do_action('loop_start');
	}



}
function bp_yt_has_items( $args = ''){
//initialize
  global $yt_template;
   $defaults = array(
		'page' => 1,
		'per_page' => 10,
		'max' => false,
                'yt_userid'=>'',
                'user_id'=>'',
                'feed_link'=>''
		);

$r = wp_parse_args( $args, $defaults );
extract( $r );
if(empty($yt_userid))
   $yt_userid=bp_yt_get_user_account($user_id);

if(empty($feed_link))
    $feed_link="http://gdata.youtube.com/feeds/base/users/".$yt_userid."/uploads?v=2&alt=json";
       
if(!empty($feed_link)){
   
    $yt_template=new BP_YT_Template($feed_link,$page,$per_page,$max);
    return apply_filters( 'bp_yt_has_items', $yt_template->has_items(), &$yt_template );
}
else
    return false;

}

function bp_yt_items(){
    global $yt_template;
	return $yt_template->items();
}

function bp_yt_the_item(){
     global $yt_template;
	return $yt_template->the_item();
}

//pagination
function bp_yt_pagination_links() {
	echo bp_yt_get_pagination_links();
}
	function bp_yt_get_pagination_links() {
		global $yt_template;

		return apply_filters( 'bp_yt_get_pagination_links', $yt_template->pag_links );
	}

function bp_yt_pagination_count() {
	global $bp, $yt_template;

	$from_num = bp_core_number_format( intval( ( $yt_template->pag_page - 1 ) * $yt_template->pag_num ) + 1 );
	$to_num = bp_core_number_format( ( $from_num + ( $yt_template->pag_num - 1 ) > $yt_template->total_item_count ) ? $yt_template->total_item_count : $from_num + ( $yt_template->pag_num - 1 ) );
	$total = bp_core_number_format( $yt_template->total_item_count );

	echo sprintf( __( 'Viewing videos %s to %s (of %s videos)', 'bp-yt' ), $from_num, $to_num, $total ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}
/** for feed */
function bp_yt_account_link(){
   echo bp_yt_get_account_link();
}
    function bp_yt_get_account_link(){
        global $yt_template;
        return $yt_template->url;
    }
function bp_yt_feed_title(){
    echo  bp_yt_get_feed_title();
}
    function bp_yt_get_feed_title(){
        global $yt_template;
        return $yt_template->title;
    }
function bp_yt_feed_description(){
    echo bp_yt_get_feed_description();
}
    function bp_yt_get_feed_description(){
        global $yt_template;
        return $yt_template->description;
    }
    //template tags for each of the video item
    //show video title
function bp_yt_item_title(){
    echo bp_yt_get_item_title();
}
    function bp_yt_get_item_title(){
        return bp_yt_get_item_data("title");
    }
   //show video discription
function bp_yt_item_description(){
    echo bp_yt_get_item_description();
}
    function bp_yt_get_item_description(){
        return bp_yt_get_item_data("description");
    }

//show current video
function  bp_yt_show_video($width=640,$height=385){
 global $yt_template;
    $item=$yt_template->item;
    $vid_id=yt_get_vid_id($item);
   echo yt_get_video_code($vid_id, $width, $height);
}
/*
 * print Link of video on youtube
 */
function bp_yt_item_url(){
    echo bp_yt_get_item_url();
}
    function bp_yt_get_item_url(){
        return bp_yt_get_item_data("url");
    }
    //thumb mid src
function bp_yt_item_mid_src(){
    echo bp_yt_get_item_mid_src();
}
//
    function bp_yt_get_item_mid_src(){
        return bp_yt_get_item_data("m_url");
    }

function bp_yt_item_thumb_src(){
    echo bp_yt_get_item_thumb_src();
}
    function bp_yt_get_item_thumb_src(){
        return bp_yt_get_item_data("t_url");
    }
    //for the larger thumb image
function bp_yt_item_large_src(){
    echo bp_yt_get_item_large_src();
}
    function bp_yt_get_item_large_src(){
        return bp_yt_get_item_data("l_url");
    }
    //date video was published
function bp_yt_item_date_published(){
    echo bp_yt_get_item_date_published();
}
    function bp_yt_get_item_date_published(){
        return bp_yt_get_item_data("date_published");
    }
//video id, eg. xvf34536363 like
function bp_yt_item_id(){
    echo bp_yt_get_item_id();
}
    function bp_yt_get_item_id(){
        return bp_yt_get_item_data("id");
    }
  //get the uploader name
function bp_yt_author_name(){
    echo bp_yt_get_author_name();
}
    function bp_yt_get_author_name(){
        return bp_yt_get_item_data("author_name");
    }
function bp_yt_item_author_url(){
    echo bp_yt_get_item_author_url();
}
    function bp_yt_get_item_author_url(){
        return bp_yt_get_item_data("author_url");
    }
function bp_yt_item_author_link(){
    echo bp_yt_get_item_author_link();
}
    function bp_yt_get_item_author_link(){
        return bp_yt_get_item_data("author_link");
    }
function bp_yt_item_tags(){
    echo bp_yt_get_item_tags();
}
    function bp_yt_get_item_tags(){
        return bp_yt_get_item_data("tags");
    }
function bp_yt_item_get_tags_as_array(){
        return bp_yt_get_item_data("tags_array");

}
  //not implemented
function bp_yt_item_license(){
    echo bp_yt_get_item_license();
}
    function bp_yt_get_item_license(){
            return bp_yt_get_item_data("item_license");

    }

////helper function
function bp_yt_get_item_data($key){
    global $yt_template;
    $item=$yt_template->item;

    switch($key){
        case "title":
            $val=$item["title"]['$t'];
            //someme
            break;
        case "description":
            $val=$item["content"]['$t'];
            //someme
            break;
        case "description_raw":
            $val=$item["content"]['$t'];
            //someme
            break;
        case "url":
            $val=$item["link"][1]['href'];
            //someme
            break;
        case "l_url":
             $val="http://img.youtube.com/vi/".  yt_get_vid_id($item)."/0.jpg";
            break;
         case "t_url":
         case "m_url":
             $val="http://img.youtube.com/vi/".  yt_get_vid_id($item)."/2.jpg";

            //someme
            break;
         case "id":
             $val=yt_get_vid_id($item);
             break;

          case "date_published":
                $val=$item['published']['$t'];
              break;
            case "author_link":
                $val=$item["author"][0]['uri']['$t'];
                break;
          case "author_name":
              $val=$item["author"][0]['name']['$t'];
                break;
        default:
             $val="";
            break;
    }
    return $val;

}


//conditional tag

function bp_yt_is_home(){
    global $bp;
    if(bp_is_current_component($bp->yt->slug)&&$bp->current_action=="my-yt")
            return true;
    return false;
}

function bp_yt_is_settings(){
    global $bp;
    if(bp_is_current_component($bp->yt->slug)&&$bp->current_action=="settings")
            return true;
    return false;
}

//map object to array, util function
function yt_obj_to_array_mapper($data){
    if(!is_object($data) && !is_array($data)) return $data;

   if(is_object($data)) $data = get_object_vars($data);

   return array_map('yt_obj_to_array_mapper', $data);
}

function yt_get_vid_id($item){
            $vid=$item["id"]['$t'];
            $start=strpos($vid,"video:")+6;
            $vid_id=substr($vid, $start);
return $vid_id;
}

//pass video id, width,height to generate the embed code
function yt_get_video_code($video_id, $width, $height){
    
        $code = "<object width=\"";
	$code .= $width;
	$code .= "\" height=\"";
	$code .= $height;
	$code .= "\"><param name=\"movie\" value=\"";
	$code .= $video_id;
	$code .= "\"></param><param name=\"wmode\" value=\"transparent\" ></param><embed src=\"http://www.youtube.com/v/";
	$code .= $video_id;
	$code .= "\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"";
	$code .= $width;
	$code .= "\" height=\"";
	$code .= $height;
	$code .= "\"></embed></object>";

	

    return $code;
}
?>