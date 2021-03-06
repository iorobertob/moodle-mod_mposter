<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     mod_inter
 * @copyright   2019 LMTA <roberto.becerra@lmta.lt>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/mposter/lib.php");


// moodle 
function mposter_set_mainfile($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;
    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $options = array('subdirs' => true, 'embed' => false);
        if ( (isset($data->display)) && ($data->display == RESOURCELIB_DISPLAY_EMBED)) {
            $options['embed'] = true;
        }
        file_save_draft_area_files($draftitemid, $context->id, 'mod_mposter', 'content', 0, $options);
    }
    $files = $fs->get_area_files($context->id, 'mod_mposter', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_mposter', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
    
    if (count($files) > 0) {
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
    }
    else{
        $url = 'no file';
    }

    return $url;
}

/**
 * Get Metadata from Resource Space based on the Metadata File added on the settings of this activity
 * @param $context   The Context of this activity / module
 * @param @mposter   The current module's instance
 */
function mposter_get_metadata($cmid, $mposter)
{
    global $DB;
    $context = context_module::instance($cmid);
    try{
        // Retrieve elements from filename divided by "_"s
        // collection[0]= collection section in filename, collection[1]=whole filename
        $collection = mposter_get_item_from_filename($context, 0, $mposter->id);

        // If there was no file then we cut short here. 
        if ($collection == null){
            return;
        }


        $DB->set_field('mposter', 'rs_collection', $collection[0], array('name' => $mposter->name));

        // Findout which ID corresponds to this file in RS
        $request_json     = mposter_get_file_fields_metadata($collection[1]);
        $resourcespace_id = $request_json[1][0]["ref"];
   
        $DB->set_field('mposter', 'rs_id', $resourcespace_id, array('name' => $mposter->name));

        // If user types metadata titles and field, they override the default titles. 
        $list_metadata[0] = ($mposter->meta1 != "" ? $mposter->meta1 : "Composer");
        $DB->set_field('mposter', 'meta1',       $list_metadata[0],  array('name' => $mposter->name));

        $list_metadata[1] = ($mposter->meta2 != "" ? $mposter->meta2 : "Title");
        $DB->set_field('mposter', 'meta2',       $list_metadata[1],  array('name' => $mposter->name));
        
        $list_metadata[2] = ($mposter->meta3 != "" ? $mposter->meta3 : "Title - English");
        $DB->set_field('mposter', 'meta3',       $list_metadata[2],  array('name' => $mposter->name));
        
        $list_metadata[3] = ($mposter->meta4 != "" ? $mposter->meta4 : "Surtitle");
        $DB->set_field('mposter', 'meta4',       $list_metadata[3],  array('name' => $mposter->name));
        
        $list_metadata[4] = ($mposter->meta5 != "" ? $mposter->meta5 : "List");
        $DB->set_field('mposter', 'meta5',       $list_metadata[4],  array('name' => $mposter->name));
        
        $list_metadata[5] = ($mposter->meta6 != "" ? $mposter->meta6 : "1st line");
        $DB->set_field('mposter', 'meta6',       $list_metadata[5],  array('name' => $mposter->name));
        
        $list_metadata[6] = ($mposter->meta7 != "" ? $mposter->meta7 : "Language");
        $DB->set_field('mposter', 'meta7',       $list_metadata[6],  array('name' => $mposter->name));   
        
        $metadata = mposter_get_metadata_from_api($resourcespace_id, $mposter, $list_metadata);

        // Commit metadata to database
        $length = count($metadata);
        for ($i = 0; $i < $length; $i++) {
            if($metadata[$i] != NULL){
                $index = $i + 1;
                $data = $metadata[$i];
                
                $DB->set_field('mposter', 'meta_value'.$index, $data,               array('name' => $mposter->name));
                $DB->set_field('mposter', 'meta'.$index,       $list_metadata[$i],  array('name' => $mposter->name));
            }
        }

    }catch (Exception $e){
        print_error("ivalidrequest", $debuginfo = $e . " : Invalid Database or API request, do you have Resourcespae rspository plugin installed?");
    }
}

/**
 * lmta.utility
 * Item is each one of the parts in a file name like: item_item_item.extension 
 * If filenames of files uploaded to this mposter contain information separated by _ (undesrcore), this 
 * function retreives one of those elements from the first of the files to upload. 
 * @param Context  $context the context of the current course
 * @param String   $item_number is the position number of the filename to get
 * @return String  $item is the piece of string from the filename of the first file in the upload. 
 */
function mposter_get_item_from_filename($context, $item_number, $id)
{
    global $DB, $CFG, $PAGE;    
    
    // // Get files array and their names, split them by '_' and return the first of those divisions. 
    $fs              = get_file_storage();
    $files           = $fs->get_area_files($context->id, 'mod_mposter', 'content', 0, 'sortorder', false);

    if (count($files) > 0){

        $keys            = array_keys($files);
        $filename        = $files[$keys[0]] -> get_filename();
        $filename_parts  = explode("_", $filename);

        $item            = $filename_parts[$item_number];

        if(count($filename_parts) > 2){
            $characteristics = $filename_parts[2];
        }

        $items[0] = $item;
        $items[1] = $filename;
    
        return $items;
    }
    else{
        return null;
    }
    
    
}

// moodle 
/**
 * File browsing support class
 */
class mposter_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

/**
 * Get the fields from the Resourcespae metadata
 */
function mposter_get_file_fields_metadata($string)
{
    $api_result = mposter_do_api_search($string, 'do_search');
    return $api_result;
}

/**
 * Do an API requeuest with 
 */
function mposter_do_api_search($string, $function)
{
    $RS_object = mposter_init_resourcespace();
    // Set the private API key for the user (from the user account page) and the user we're accessing the system as.
    $private_key = $RS_object->api_key;

    $user="admin";
    $user = $RS_object->api_user;

    $url = $RS_object->resourcespace_api_url ;
    // Formulate the query
    $query="user=" . $user . "&function=".$function."&param1=".$string."&param2=&param3=&param4=&param5=&param6=";

    // Sign the query using the private key
    $sign=hash("sha256",$private_key . $query);

    // Make the request and output the JSON results.
    $results=json_decode(file_get_contents($url . $query . "&sign=" . $sign));
    $results=file_get_contents($url . $query . "&sign=" . $sign);
    $results=json_decode(file_get_contents($url . $query . "&sign=" . $sign), TRUE);
    
    $result = [];
    $result[0] = "https://resourcespace.lmta.lt/api/?" . $query . "&sign=" . $sign;
    $result[1] = $results;

    return $result;
}

/**
 * Initialise Resourcespace API variables
 */
function mposter_init_resourcespace()
{
    $RS_object = new stdClass;
    $RS_object->config          = get_config('resourcespace');
    $RS_object->resourcespace_api_url = get_config('resourcespace', 'resourcespace_api_url');
    $RS_object->api_key         = get_config('resourcespace', 'api_key');
    $RS_object->api_user        = get_config('resourcespace', 'api_user');
    $RS_object->enable_help     = get_config('resourcespace', 'enable_help');
    $RS_object->enable_help_url = get_config('resourcespace', 'enable_help_url');

    return $RS_object;
}

/**
 * Get the data via API call and compare its metadata with the one indicated in the current Inter list instance
 */
function mposter_get_metadata_from_api($resourcespace_id, $moduleinstance, $list_metadata)
{
    global $PAGE, $DB, $CFG;
    $prefix = $CFG->prefix;

    $result = mposter_do_api_search($resourcespace_id, 'get_resource_field_data');

    $new_list_metadata = array_fill(0, sizeof($list_metadata), '');
    for($i = 0; $i < sizeof($list_metadata); $i++)
    {
        foreach($result[1] as $row)
        {
            if ($row["title"] === $list_metadata[$i])
            {
                $new_list_metadata[$i] = $row["value"];
            }
        }
    } 
    return $new_list_metadata;
}
