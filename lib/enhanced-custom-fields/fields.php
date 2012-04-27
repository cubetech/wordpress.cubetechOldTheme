<?php
class ECF_Field {
	var $type;
	
	var $default_value;
	
	var $value, $values = array();

	var $post_id;

    // The ID in postmeta table
	var $meta_id;

	var $id;

	var $is_subfield = false;

	// whether this custom field can have more than one value
	var $is_multiply = false;

	// ECF_Panel sets this once the field is attached to panel object
	var $current_post_type = null;

	// whether this custom field can not be empty
	var $_is_required = false;

	var $labels = array(
		'add_field'=>'Add field ...',
		'delete_field'=>'Delete field ...',
	);

	function factory($type, $name, $label=null) {
		$type = str_replace(" ", '', ucwords(str_replace("_", ' ', $type)));

		$class = "ECF_Field$type";

		if (!class_exists($class)) {
			ecf_conf_error("Cannot add meta field $type -- unknown type. ");
		}

		// Try to guess field label from it's name
		if (is_null($label)) {
			// remove the leading underscore(if it's there)
			$label = preg_replace('~^_~', '', $name);
			// split the name into words and make them capitalized
			$label = ucwords(str_replace('_', ' ', $label));
		}

		if (substr($name, 0, 1)!='_') {
			// add underscore to custom field name -- this will remove it from
			// custom fields list in administration
			$name = "_$name";
		}
		$field = new $class($name, $label);
		$field->type = $type;
	    return $field;
	}

	function ECF_Field($name, $label) {
	    $this->name = $name;
	    $this->label = $label;

	    $random_string = md5(mt_rand() . $this->name . $this->label);
	    $random_string = substr($random_string, 0, 5); // 5 chars should be enough
	    $this->id = 'ecf-'. $random_string;

	    $this->init();
	    if (is_admin()) {
			$this->admin_init();
		}
		add_action('admin_init', array(&$this, 'wp_init'));
	}

	function load() {
        global $wpdb;
		if (empty($this->post_id)) {
			ecf_conf_error("Cannot load -- unknown POST ID");
		}
        if ($this->is_subfield) {
            # the field is already loaded by the multiply field... 
            return;
        }
		$single = true;
		if ($this->is_multiply) {
			$single = false;
		}
        $meta_info = $wpdb->get_results("
            SELECT `meta_id`, `meta_value`
            FROM " . $wpdb->prefix . "postmeta
            WHERE `post_id`=" . intval($this->post_id) . "
            AND `meta_key`='" . $this->name . "'
		");
        

        if ( $this->is_multiply ) {
			$this->values = $meta_info;
			$this->value = '';
		} elseif( isset($meta_info[0]) ) {
            $this->meta_id = $meta_info[0]->meta_id;
            $this->value = maybe_unserialize($meta_info[0]->meta_value);
        }
	}

	// abstract init methods

	function init() {}
	function admin_init() {}
	function wp_init() {}
	/* / */

	function multiply() {
		$this->is_multiply = true;
	    return $this;
	}

	function setup_labels($labels) {
	    $this->labels = array_merge($this->labels, $labels);
	    return $this;
	}

	function set_default_value($default_value) {
	    $this->default_value = $default_value;
	    return $this;
	}

	function help_text($help_text) {
		$this->help_text = $help_text;
		return $this;
	}

	function render_row($field_html) {
		$help_text = isset($this->help_text) ? '<p class="ecf-description" rel="' . $this->id . '">' . $this->help_text . '</p>' : '' ;

		$field_has_options = $this->is_multiply || $this->is_subfield;

		$html = '
		<tr class="ecf-field-container">
			<td class="ecf-label"><label for="' . $this->id . '">' . $this->label . '</label></td>
			<td ' . ($field_has_options ? '' : 'colspan="2"') . '>' . $field_html . $help_text . '
		';

        if ($this->is_subfield) {
            $html .= '<input type="hidden" name="' . $this->name . '_meta_id[' . $this->id . ']" value="' . $this->meta_id . '" />';
        }

        $html .= '</td>';

		if ($this->is_multiply) {
			$html .= '<td class="ecf-action-cell"><a href="#" class="clone-ecf ecf-action">' . $this->labels['add_field'] . '</a></td>';
		} else if ($this->is_subfield) {
			$html .= '<td class="ecf-action-cell"><a href="#" class="delete-ecf ecf-action">' . $this->labels['delete_field'] . '</a>';
			$html .= '<input type="hidden" name="' . $this->name . '_original_vals[' . $this->id . ']" value="' . esc_attr($this->value) . '" />';
			$html .= '</td>';
		}
		$html .= '</tr>';
		return $html;
	}

	function set_value_from_input() {
		if (!isset($_POST[$this->name])) {
			return;
		}
		if ( $this->is_multiply ) {
			$this->values = $_POST[$this->name];
		} else {
			$value = $_POST[$this->name];
			$this->value = $value;
		}
	}

	// abstract method
	// Called before delete_post_meta
	function delete($value) {}

	function save() {
		global $wpdb;
		if ($this->is_multiply) {
			foreach ($this->values as $val) {
				if ($val) {
					if ( is_object($val) && isset($val->meta_id) ) {
						# $values untouched from load() - nothing to do
						continue;
					} else {
						add_post_meta($this->post_id, $this->name, $val);
					}
				}
			}
			if (isset($_POST[$this->name . "_original_vals"])) {
				foreach ($_POST[$this->name . "_original_vals"] as $key => $original_value) {
					// deleting value actually removes the field from the form
					if (!isset($_POST[$this->name . "_updated_vals"][$key])) {
						$this->delete($original_value);
						$this->delete_meta($this->post_id, $this->name, $original_value);
						continue;
					}
					$updated_value = $_POST[$this->name . "_updated_vals"][$key];

					// empty value removes the field
					if (empty($updated_value)) {
						$this->delete($original_value);
						$this->delete_meta($this->post_id, $this->name, $original_value);
					}
					if (isset($_POST[$this->name . '_meta_id'][$key])) {
						$meta_id = $_POST[$this->name . '_meta_id'][$key];
						$this->update_meta($meta_id, $this->name, $updated_value);
					}
				}
			}
		} else {
			update_post_meta($this->post_id, $this->name, $this->value);
		}

	}

	function update_meta( $meta_id, $meta_key, $meta_value ) {
		global $wpdb;

		$meta_key = stripslashes($meta_key);

		if ( '' === trim( $meta_value ) )
			return false;

		$post_id = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_id = %d", $meta_id) );

		$meta_value = maybe_serialize( stripslashes_deep( $meta_value ) );
		$meta_id = (int) $meta_id;

		$data  = compact( 'meta_key', 'meta_value' );
		$where = compact( 'meta_id' );

		do_action( 'update_postmeta', $meta_id, $post_id, $meta_key, $meta_value );
		$rval = $wpdb->update( $wpdb->postmeta, $data, $where );
		wp_cache_delete($post_id, 'post_meta');
		do_action( 'updated_postmeta', $meta_id, $post_id, $meta_key, $meta_value );

		return $rval;
	}

    function delete_meta( $object_id, $meta_key, $meta_value ) {
		global $wpdb;

		// expected_slashed ($meta_key)
        $meta_key = stripslashes($meta_key);
		$meta_value = maybe_serialize( stripslashes_deep($meta_value) );

		$query = $wpdb->prepare( "SELECT `meta_id` FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key );

		$query .= $wpdb->prepare(" AND post_id = %d", $object_id );

		if ( $meta_value )
			$query .= $wpdb->prepare(" AND meta_value = %s", $meta_value );

		$query .= 'LIMIT 1';

		$meta_ids = $wpdb->get_col( $query );
		if ( !count( $meta_ids ) )
			return false;

		do_action( "delete_post_meta", $meta_ids, $object_id, $meta_key, $meta_value );

		$query = "DELETE FROM $wpdb->postmeta WHERE `meta_id` IN( " . implode( ',', $meta_ids ) . " )";

		$count = $wpdb->query($query);

		if ( !$count )
			return false;

		wp_cache_delete($object_id, 'post_meta');
		do_action( "deleted_post_meta", $meta_ids, $object_id, $meta_key, $meta_value );

		return true;
    }

	// abstract method
	function render() {}

	function build_html_atts($tag_atts) {
	    $default = array(
	    	'class'=>'ecf-field ecf-' . strtolower(get_class($this)),
	    	'id'=>$this->id,
	    	'rel'=>$this->id,
	    );
	    if ($this->_is_required) {
	    	$default['class'] .= ' required';
	    }

	    if (isset($tag_atts['class'])) {
	    	$tag_atts['class'] .= ' ' . $default['class'];
	    }

	    if ($this->is_multiply) {
	    	$tag_atts['name'] .= '[]';
	    } else if ($this->is_subfield) {
	    	$tag_atts['name'] .= '_updated_vals[' . $this->id . ']';
	    }

	    return array_merge($default, $tag_atts);
	}

	// Builds HTML for tag.
	// example usage:
	// echo $this->build_tag('strong', array('class'=>'red'), 'I'm bold and red');
	// ==> <strong class="red">I'm bold and red</strong>
	function build_tag($tag, $atts, $content=null) {
	    $atts_text = '';
	    foreach ($atts as $key=>$value) {
	    	$atts_text .= ' ' . $key . '="' . esc_attr($value) . '"';
	    }

	    $return = '<' . $tag . $atts_text;
	    if (!is_null($content)) {
	    	$return .= '>' . $content . '</' . $tag . '>';
	    } else {
	    	$return .= ' />';
	    }
	    return $return;
	}

	function render_field() {
		$return = '';
		if ($this->is_multiply) {
			foreach ($this->values as $val) {
				// create new field object.
				$field = ECF_Field::factory($this->type, $this->name, $this->label);
				$field->post_id = $this->post_id;
				$field->meta_id = $val->meta_id;
				$field->value = $val->meta_value;
				$field->is_subfield = true;
				
				if (isset($this->options) && $this->options) {
					$field->add_options($this->options);
				}
				$return .= $field->render();
			}
		}
		$return .= $this->render();
		return $return;
	}
	function required() {
		$this->_is_required = true;
		return $this;
	}
}
class ECF_FieldText extends ECF_Field {
	function render() {
    	if (!isset($_GET['post'])) {
	    	$this->value = $this->default_value;
	    }
		$input_atts = $this->build_html_atts(array(
			'type'=>'text',
			'name'=>$this->name,
			'value'=> $this->value,
			'value'=> (isset($this->value) ? $this->value : ( isset($this->default_value) ? $this->default_value : '') ),
		));
		$field_html = $this->build_tag('input', $input_atts);

	    return $this->render_row($field_html);
	}
}

class ECF_FieldTextarea extends ECF_Field {
	var $rows = 2;

	function rows($rows = 2) {
		$this->rows = $rows;
		return $this;
	}

	function render($append = '') {
		$atts = array(
			'name'=>$this->name,
			'rows'=>$this->rows,
		);

		$textarea_atts = $this->build_html_atts($atts);
		$val = (isset($this->value) ? $this->value : ( isset($this->default_value) ? $this->default_value : '') );
		$field_html = $this->build_tag('textarea', $textarea_atts, $val);

		return $this->render_row($field_html . $append);
	}
}

class ECF_FieldRichText extends ECF_FieldTextarea {
	var $rows = 10;
	
	function rows($rows) {
		$this->rows = $rows;
		return $this;
	}
	
	function render() {
		global $wp_version;
		ob_start();
		if (version_compare($wp_version, '3.3') >= 0) {
			wp_editor($this->value, $this->name, array('textarea_rows'=>intval($this->rows)));
		} else {
			the_editor($this->value, $this->name);
		}
		$html = ob_get_clean();
		return $this->render_row($html);
	}
}

class ECF_FieldSelect extends ECF_Field {
	var $options = array();
	function add_options($options) {
	    $this->options = $options;
	    return $this;
	}
    function render() {
    	if (empty($this->options)) {
    		ecf_conf_error("Add some options to $this->name");
    	}
		$options = '';
		foreach ($this->options as $key=>$value) {
			$options_atts = array('value'=>$key);
			if ($this->value==$key) {
				$options_atts['selected'] = "selected";
			}
			$options .= $this->build_tag('option', $options_atts, $value);
		}
		$select_atts = $this->build_html_atts(array(
			'name'=>$this->name,
		));
		$select_html = $this->build_tag('select', $select_atts, $options);

	    return $this->render_row($select_html);
	}
	function multiply() {
	    ecf_conf_error(get_class($this) . " cannot be multiply");
	}
}

class ECF_FieldFile extends ECF_Field {
	public $use_flash = false;
	public $allowed_extensions = array();

	function admin_init() {
		wp_enqueue_script('swfupload', get_bloginfo('template_directory') . '/lib/enhanced-custom-fields/tpls/swfupload/swfupload.js');
		wp_enqueue_script('swfupload-config', get_bloginfo('template_directory') . '/lib/enhanced-custom-fields/tpls/swfupload/swfupload.config.js');
		wp_enqueue_script('swfobject', get_bloginfo('template_directory') . '/lib/enhanced-custom-fields/tpls/swfobject/swfobject.js');

		if (isset($_GET['ecf-upload-service']) && isset($_FILES[$this->name])) {
			$result = $this->set_value_from_input();
			if (substr($result, 0, strlen($this->name)) == $this->name) {
				echo 'ok:';
			}
			echo $result;
			exit;
		}
	}

	function restrict_extensions($extensions) {
		$this->allowed_extensions = $extensions;
		return $this;
	}

	function render() {
	    $atts = $this->build_html_atts(array(
		    'type'=>'file',
		    'name'=>$this->name,
	    ));

	    $input_html = '<div id="' . $this->name . '-ecf-wrap" class="ecf-upload-field-wrap">';

	    if ($this->use_flash) {
	    	ob_start();
		    include('tpls/upload.php');
		    $input_html .= ob_get_clean();
	    }

	    $input_html .= '<div class="ecf-upload-field-browser">' . $this->build_tag('input', $atts) . '</div>';
	    if ( !empty($this->value) ) {
	    	$input_html .= '<div class="ecf-file-description">';
	    	$input_html .= $this->get_file_description();
	    	$input_html .= '&nbsp;<a href="' . add_query_arg(array('delete_field' => $this->name, 'delete_value' => urlencode($this->value))) . '" class="delete-file">Delete</a>';

		    if ($this->is_subfield) {
		    	$input_html .= '<input type="hidden" name="' . $this->name . '_updated_vals[' . $this->id . ']" value="' .  $this->value. '">';
		    }
		    $input_html .= '</div>';
	    }

	    $input_html .= '</div>';

	    return $this->render_row($input_html);
	}

	function use_flash() {
		$this->use_flash = true;
		return $this;
	}

	function get_file_description() {
	    return '<a href="' . get_upload_url() . '/' . $this->value . '" alt="" class="ecf-view_file" target="_blank">View File</a>';
	}

	function load() {
	    ECF_Field::load();
	    
	    // check for delete request
		if (isset($_GET['delete_field']) && $_GET['delete_field']==$this->name && isset($_GET['delete_value']) && is_admin() ) {
			$delete_value = urldecode($_GET['delete_value']);
			$redirect_url = remove_query_arg(array('delete_field', 'delete_value'));
			if ( !$this->is_multiply && $delete_value != $this->value ) {
				header('Location: ' . $redirect_url);
				exit();
			} elseif( $this->is_multiply ) {
				// go trhough every $this->values entry and check for equal meta_value
				$found = false;
				foreach ($this->values as $val) {
					if ( $val->meta_value == $delete_value ) {
						$found = true;
						break;
					}
				}

				if ( !$found ) {
					header('Location: ' . $redirect_url);
					exit();
				}
			}
			
			$this->delete($delete_value);
			$this->delete_meta($this->post_id, $this->name, $delete_value);
			header('Location: ' . $redirect_url);
			exit();
		}
	}

	function set_value_from_input() {
		$this->values = array();

		if (empty($_FILES[$this->name]) && empty($_POST[$this->name . '_swf_upload_value'])) {
			return;
		} elseif ( !empty($_POST[$this->name . '_swf_upload_value']) ) {
			$this->set_value($_POST[$this->name . '_swf_upload_value']);
			return;
		}

		$files_queue = array();
		$files_saved = array();

		$files = $_FILES[$this->name];

		if ( $this->is_multiply && !empty($_FILES[$this->name . '_updated_vals']) ) {
			foreach ($_FILES[$this->name . '_updated_vals'] as $key => $fields) {
				foreach ($fields as $field_id => $value) {
					$new_index = count($files[$key]);
			 		$files[$key][$new_index] = $value;
			 		$files['is_update'][$new_index] = $field_id;
				}
			 }
		}

		// use the same files array format for single and multiply fields
		if ( is_array($files['name']) ) {
			foreach ($files['name'] as $i => $file_name) {
				$files_queue[] = array(
					'name' => $files['name'][$i],
					'type' => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error' => $files['error'][$i],
					'size' => $files['size'][$i],
					'is_update' => (isset($files['is_update'][$i]) ? $files['is_update'][$i] : false ),
				);
			}
		} else {
			$files_queue[] = $files;
		}

		// process file upload/update
		foreach ($files_queue as $file) {
			if ($file['error'] != 0) { continue; }
			if ($this->allowed_extensions) {
				$extension = '.' . preg_replace('~^.*\.~', '', $file['name']);
				if (!in_array($extension, $this->allowed_extensions)) {
					return 'The file you tried to upload was of an unsupported filetype.';
				}
			}

			if ( isset($file['is_update']) && $file['is_update'] ) {
				// when set, $file['is_update'] is the field's id (e.g. ecf-abcdf)
				$new_file = $this->save_file($file);
				$this->delete($_POST[$this->name . "_updated_vals"][$file['is_update']]);
				$_POST[$this->name . "_updated_vals"][$file['is_update']] = $new_file;
			} else {
				$files_saved[] = $this->save_file($file);
			}
		}

		if ( $this->is_multiply ) {
        	$this->values = $files_saved;
		} elseif( isset($files_saved[0]) ) {
        	$this->value = $files_saved[0];
		}
		return $files_saved[0];
	}

	function get_upload_path() {
		$upload_path = get_option( 'upload_path' );
		$upload_path = trim($upload_path);
		if ( empty($upload_path) || realpath($upload_path) == false ) {
			$upload_path = get_upload_dir();
		}
		return $upload_path;
	}

	function modify_file($file_dest) {
		// placeholder for image resizing, for example
	}

	function save_file($file) {
		// Build destination path
		$upload_path = $this->get_upload_path();

		$file_ext = array_pop(explode('.', $file['name']));

		// Build file name (+path)
		$file_path = $this->name . '/' . $this->post_id . '-' . substr(md5(rand()), 24) . '.' . $file_ext;

		$file_dest = $upload_path . DIRECTORY_SEPARATOR . $file_path;
		if ( !file_exists( dirname($file_dest) ) ) {
			mkdir( dirname($file_dest) );
		}

		if ( !empty($this->value) && $this->value != $file_path) {
			if ( file_exists($upload_path . DIRECTORY_SEPARATOR . $this->value) ) {
				unlink($upload_path . DIRECTORY_SEPARATOR . $this->value);
			}
		}

		// Move file
		if ( move_uploaded_file($file['tmp_name'], $file_dest) != FALSE ) {
			$this->modify_file($file_dest);
	    	return $file_path;
		}
	}

	function delete($value) {
		$upload_path = $this->get_upload_path();
		if ( file_exists($upload_path . DIRECTORY_SEPARATOR . $value) ) {
			unlink($upload_path . DIRECTORY_SEPARATOR . $value);
		}
	}
}

class ECF_FieldImage extends ECF_FieldFile {
	var $width, $height;

	function set_size($width, $height) {
	    $this->width = intval($width);
	    $this->height = intval($height);
	    return $this;
	}

	function get_file_description() {
	    return '<img src="' . get_upload_url() . '/' . $this->value . '" alt="" height="100" class="ecf-view_image"/>';
	}

	function modify_file($file_dest) {
		if ( !($this->width == null && $this->height == null)) {
			$resized = image_resize($file_dest , $this->width, $this->height, true, 'tmp');
			// Check if image was resized
			if ( is_string($resized) ) {
				if ( file_exists($file_dest)) {
					unlink($file_dest);
				}
				rename($resized, $file_dest);
			}
		}
	}
}


class ECF_FieldSeparator extends ECF_Field {
	function render() {
		$field_html = '';
	    return $this->render_row($field_html);
	}
	function render_row($field_html) {
	    return '
		<tr class="ecf-field-container">
			<td class="ecf-label">&nbsp;</td>
			<td>' . (( !empty($this->label) ) ? '<strong>' . $this->label . '</strong>' : '') . '&nbsp;</td>
		</tr>
		';
	}
	function multiply() {
	    ecf_conf_error(get_class($this) . " cannot be multiply");
	}
	function save() {
		// skip saving
	}
}

class ECF_FieldMap extends ECF_Field {
	var $lat=37.423156, $long=-122.084917, $zoom=14;
	
	function init() {
		$this->help_text = 'Double click on the map and marker will appear. Drag &amp; Drop the marker to new position on the map.';
		ECF_Field::init();
	}
	function render() {
		ob_start();
		include ('tpls/ecf_fieldmap.php');
	    return $this->render_row(ob_get_clean());
	}
	function set_position($lat, $long, $zoom) {
		$this->lat = $lat;
		$this->long = $long;
		$this->zoom = $zoom;

		return $this;
	}
	function multiply() {
	    ecf_conf_error(get_class($this) . " cannot be multiply");
	}
}

class ECF_FieldAddress extends ECF_FieldTextarea {
	function render() {
		return parent::render('<a href="#" id="locate-on-map" style="margin-left: 10px;">Locate address on map ... </a>');
	}
	function admin_init() {
		add_action('admin_footer', array($this, 'print_the_js'));
	}
	function print_the_js() {
	    ?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('#locate-on-map').click(function () {
				    var address = $("#<?php echo $this->id ?>").val();
					var geocoder = new google.maps.Geocoder();
					geocoder.geocode( 
						{ 'address': address}, 
						function(results, status) {
				            if (status == google.maps.GeocoderStatus.OK) {
				            	var coords = results[0].geometry.location;
				                var _map = get_map();

				                set_coords(coords);
				                _map.setCenter(coords);
				            } else {
				                alert(address + " not found");
			            	}
			        	}
			    	);
					return false;
				});
			});
		</script>
		<?php
	}
}

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'new-files.php');

class ECF_FieldDate extends ECF_Field {
	function init() {
		if (defined('WP_ADMIN') && WP_ADMIN) {
			wp_enqueue_script('jqueryui-datepicker', get_bloginfo('stylesheet_directory') . '/lib/enhanced-custom-fields/tpls/jqueryui/jquery-ui-1.7.3.custom.min.js');
			wp_enqueue_style('jqueryui-datepicker', get_bloginfo('stylesheet_directory') . '/lib/enhanced-custom-fields/tpls/jqueryui/ui-lightness/jquery-ui-1.7.3.custom.css');
			wp_enqueue_script('jqueryui-initiate', get_bloginfo('stylesheet_directory') . '/lib/enhanced-custom-fields/tpls/jqueryui/initiate.js');
		}
		ECF_Field::init();
	}
	function render() {
		if ( empty( $this->value ) && !empty($this->default_value) ) {
			$this->value = $this->default_value;
		}
		$input_atts = $this->build_html_atts(array(
			'type'=>'text',
			'name'=>$this->name,
			'value'=>$this->value,
			'class'=>'datepicker-me',
		));
		$field_html = $this->build_tag('input', $input_atts);

	    return $this->render_row($field_html);
	}
}
class ECF_FieldChooseSidebar extends ECF_FieldSelect {
	// Whether to allow the user to add new sidebars
	var $allow_adding = true;
	var $sidebar_options = array(
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	);

	function init() {
		$this->add_sidebar_opts_sidebars();
		// add_action('admin_init', array($this, 'setup_sidebars'));
	}
	function add_sidebar_opts_sidebars() {
		$sidebars = $this->_get_sidebars();

		global $wp_registered_sidebars;
		foreach ($wp_registered_sidebars as $sidebar) {
			$sidebars[] = $sidebar['name'];
		}

		$options = array();

		foreach ($sidebars as $sidebar) {
			$options[$sidebar] = $sidebar;
		}

		$this->add_options($options);
		add_action('admin_footer', array($this, '_print_js'));

	    ECF_FieldSelect::init();
	}
	function disallow_adding_new() {
	    $this->allow_adding = false;
	    return $this;
	}
	function set_sidebar_options($sidebar_options) {
		// Make sure that all needed fields are in the options array
		foreach ($this->sidebar_options as $key => $value) {
			if (!isset($sidebar_options[$key])) {
				ecf_conf_error("Provide all sidebar options for $this->name ECF: <code>" .
					implode(', ', array_keys($this->sidebar_options)) . "</code>");
			}
		}
	    $this->sidebar_options = $sidebar_options;
	    return $this;
	}
	function render() {
	    if ($this->allow_adding) {
			$this->options['new'] = "Add New";
		}
		return ECF_FieldSelect::render();
	}
	function setup_sidebars() {
		$sidebars = $this->_get_sidebars();
		foreach ($sidebars as $sidebar) {
			$associated_pages = get_posts(array(
				'post_type' => array('page'),
				'meta_query'=>array(
					array( 'key' => $this->name, 'value' => urlencode($sidebar))
				),
				'numberposts'=>-1,
			));

			if (count($associated_pages)) {
				$show_pages = 5;
				$assoicated_pages_titles = array();
				$i = 0;
				foreach ($associated_pages as $associated_page) {
					$assoicated_pages_titles[] = apply_filters('the_title', $associated_page->post_title);
					if ($i==$show_pages) {
						break;
					}
					$i++;
				}
				$msg = 'This sidebar is used on ' . implode(', ', $assoicated_pages_titles) . ' ';
				if (count($associated_pages) > $show_pages) {
					$msg .= ' and ' . count($associated_pages) - $show_pages . ' more pages';
				}
			} else {
				$msg = '';
			}

			$slug = strtolower(preg_replace('~-{2,}~', '', preg_replace('~[^\w]~', '-', $sidebar)));

			register_sidebar(array(
				'name'=>$sidebar,
				'id'=>$slug,
				'description'=>$msg,
			    'before_widget' => $this->sidebar_options['before_widget'],
			    'after_widget' => $this->sidebar_options['after_widget'],
			    'before_title' => $this->sidebar_options['before_title'],
			    'after_title' => $this->sidebar_options['after_title'],
			));
		}
	}

	function _print_js() {
		?>
		<script type="text/javascript" charset="utf-8">
	      jQuery(function ($) {
	          $('#<?php echo $this->id ?>').change(function () {
	              if ($(this).val()=='new') {
	                var new_sidebar = window.prompt("Please enter the name of the new sidebar: ");
	                if ( new_sidebar==null || new_sidebar=='') {
	                  $(this).find('option:first').attr('selected', true);
	                  return false;
	                }
	                var opt = $('<option value="' + new_sidebar + '">' + new_sidebar + '</option>').insertBefore($(this).find('option:last'));
	                $(this).find('option').attr('selected', false);
	                opt.attr('selected', true);
	              }
	          });
	      });
	    </script>
		<?php
	    // include_once(dirname(__FILE__) . '/tpls/ecf_choose-sidebar-js.php');
	}

	function _get_sidebars() {
		$pages_with_sidebars = get_posts(array(
			'post_type' => array('page'),
			'meta_query'=>array(
				array('key'=>$this->name)
			),
			'numberposts'=>-1,
		));

		$sidebars = array();
		foreach ($pages_with_sidebars as $page_with_sidebar) {
			$sidebar = get_post_meta($page_with_sidebar->ID, $this->name, 1);
			if ($sidebar) {
				$sidebars[$sidebar] = 1;
			}
		}

		$sidebars = array_keys($sidebars);

		return $sidebars;
	}
}

class ECF_FieldSet extends ECF_Field {
	var $options = array();
	var $limit_options = 0;
	function add_options($options) {
	    $this->options = $options;
	    return $this;
	}
	function limit_options($limit) {
		$this->limit_options = $limit;
	}
    function render() {
    	if (!is_array($this->value)) {
    		$this->value = array($this->value);
    	}
    	if (empty($this->options)) {
    		ecf_conf_error("Add some options to $this->name");
    	}
		$options = '';
		$loopCount = 0;
		foreach ($this->options as $key=>$value) {
			$loopCount ++;
			$options_atts = array(
				'type'=>'checkbox',
				'name'=>$this->name . '[]',
				'value'=>$key,
				'style'=>'margin-right: 5px;',
			);
			if (in_array($key, $this->value)) {
				$options_atts['checked'] = "checked";
			}
			$options_atts = $this->build_html_atts($options_atts);

			if ( $this->limit_options > 0 && $loopCount > $this->limit_options ) {
				$options .= '<p style="display:none">' . $this->build_tag('input', $options_atts, $value) . '</p>';
			} else {
				$options .= '<p>' . $this->build_tag('input', $options_atts, $value) . '</p>';
				if ( $loopCount == $this->limit_options ) {
					$options .= '<p>... <a href="#" class="ecf-set-showall">Show All Options</a></p>';
				}
			}
		}

	    return $this->render_row('<div class="ecf-set-list">' . $options . '</div>');
	}

	function save() {
		if (isset($_POST[$this->name])) {
			update_post_meta($this->post_id, $this->name, $_POST[$this->name]);
		} else {
			update_post_meta($this->post_id, $this->name, array());
		}
	}

	function multiply() {
	    ecf_conf_error(get_class($this) . " cannot be multiply");
	}
}
# select box with options posts from particular post type
class ECF_FieldForeignKey extends ECF_FieldSelect {
	var $post_type = null, $is_filter_on_view_all = false, $viewall_entries_filtered = false, $is_optional = false;
	function set_post_type($post_type) {
		/* unvalidated, will be checked later(on WordPress init) */
		$this->post_type = $post_type;
		return $this;
	}
	function set_filter_on_view_all() {
		$this->is_filter_on_view_all = true;
	    return $this;
	}
	function optional() {
		$this->is_optional = true;
		return $this;
	}
	function wp_init() {
	    if (!post_type_exists($this->post_type)) {
			ecf_conf_error("Unexsiting post type: $this->post_type");
		}
		if (is_admin()) {
			$should_show_filter = false;
			# for custom post types and pages
			if (isset($_GET['post_type']) && $_GET['post_type']==$this->current_post_type) {
				$should_show_filter = true;
			}
			# for regular posts
			if (!isset($_GET['post_type']) && $this->current_post_type=='post') {
				$should_show_filter = true;
			}

			if ($this->is_filter_on_view_all && $should_show_filter) {
		    	add_action('restrict_manage_posts', array(&$this, 'print_view_all_filters'));
		    	add_action('pre_get_posts', array(&$this, 'filter_view_all_entries'));
		    }
		}
	}
	function filter_view_all_entries($q) {
		if (!$this->viewall_entries_filtered && isset($_GET['_' . $this->post_type])) {
			$this->viewall_entries_filtered = true;
			$q->set('meta_key', $this->name);
			$q->set('meta_value', intval($_GET['_' . $this->post_type]));
		}
	    return $q;
	}
	function print_view_all_filters() {
	    $this->lazy_loader();

	    $post_type_obj = get_post_type_object($this->post_type);

	    $filter_name = "_$this->post_type";
	    echo '<select name="' . $filter_name . '">';
	    echo "<option value=''>Show All " . $post_type_obj->labels->name . "</option>";
		foreach ($this->options as $id => $title) {
			$selected = '';
			if (isset($_GET[$filter_name]) && $_GET[$filter_name]==$id) {
				$selected = 'selected="selected"';
			}
			echo "<option value='$id' $selected>$title</option>";
		}
		echo '</select>';
	}
	function lazy_loader() {
		# hit the database only when it's reaaaaaly needed
	    $entries = get_posts('showposts=-1&post_type=' . $this->post_type);
		$entries_map = array();
		if ($this->is_optional) {
			$entries_map['0'] = 'Choose one (optional)';
		}
		foreach ($entries as $entry) {
			$entries_map[$entry->ID] = apply_filters('the_title', $entry->post_title);
		}
		$this->options = $entries_map;
	}
	function render() {
		$this->lazy_loader();
	    return ECF_FieldSelect::render();
	}
}

class ECF_FieldColor extends ECF_Field {
	function init() {
		if (defined('WP_ADMIN') && WP_ADMIN) {
	        $token = wp_create_nonce(mt_rand());
	        $this->html_class_name = "colorpicker_$token";
			wp_enqueue_script('custom-colorpicker', get_bloginfo('stylesheet_directory') . '/lib/enhanced-custom-fields/colorpicker/colorpicker.js');
			wp_enqueue_style('custom-colorpicker', get_bloginfo('stylesheet_directory') . '/lib/enhanced-custom-fields/colorpicker/colorpicker.css');
		}
		ECF_Field::init();
		add_action('admin_footer', array($this, 'print_js'));
	}

    function render() {
    	if (!$this->default_value) {
    		$this->default_value = '#666666';
    	}

    	$curr_value = ($this->value) ? ($this->value) : $this->default_value;

        $field_html = '<input type="text" readonly="readonly" name="' . $this->name . '" value="' . $curr_value . '" id="' . $this->name . '" class="' . $this->html_class_name . '" /><span style="background: ' . $curr_value . ';" class="color-preview">&nbsp;</span>';
	    return $this->render_row($field_html);
	}
    function print_js() {
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(function ($) {
                $('.color-preview').click(function () {
                    $(this).prev().click();
                })
                $('.<?php echo $this->html_class_name ?>').ColorPicker({
                    onChange: function (e, hex) {
                        $('.<?php echo $this->html_class_name ?>').val('#' + hex);
                        $('.<?php echo $this->html_class_name ?>').next().css('background', '#' + hex);
                    },
                    onSubmit: function(hsb, hex, rgb, el) {
                        $(el).ColorPickerHide();
                    },
                    color: '<?php echo $this->value ?>',
                });
            });
        </script>
        <?php
    }
}
# select box with options - pages
class ECF_FieldChoosePages extends ECF_FieldSelect {

	function lazy_loader() {
		# hit the database only when it's reaaaaaly needed
		$raw_pages = get_pages();
		$nice_pages = array();
		foreach ($raw_pages as $p) {
			$nice_pages[$p->ID] = $p->post_title;
		}

		$this->options = $nice_pages;
	}
	function render() {
		$this->lazy_loader();
	    return ECF_FieldSelect::render();
	}
}
# media library upload/select field
class ECF_FieldMedia extends ECF_Field {
	public $image_extensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

	function render() {
		$input_atts = $this->build_html_atts(array(
			'type'=>'text',
			'name'=>$this->name,
			'value'=>$this->value,
		));
		$field_html = $this->build_tag('input', $input_atts);

		$image = ( $this->value != '' && in_array(array_pop(explode('.', $this->value)), $this->image_extensions) )? '<br /><img src="' . $this->value . '" alt="" height="100" class="ecf-view_image"/>' : '';
		$field_html .= '<input id="c2_open_media' . str_replace('-', '_', $this->id) .  '" rel="media-upload.php?type=image" type="button" class="button-primary" value="Select Media" />' . $image;

		return $this->render_row($field_html);
	}
	function admin_init() {
		add_action('admin_print_styles-post.php', array($this, 'add_correct_script_hooks'), 1);
		add_action('admin_print_styles-post-new.php', array($this, 'add_correct_script_hooks'), 1);
		add_action('admin_footer-post.php', array($this, 'print_the_js'), 1);
		add_action('admin_footer-post-new.php', array($this, 'print_the_js'), 1);
	}
	function add_correct_script_hooks() {
		wp_enqueue_script('utf8_decode_js_userialize', get_bloginfo('stylesheet_directory') . '/lib/scripts/utf8.decode.js.unserialize.js');
		wp_enqueue_script('fancybox', get_bloginfo('stylesheet_directory') . '/lib/scripts/fancybox/jquery.fancybox-1.3.4.pack.js');
		wp_enqueue_style('fancybox-css', get_bloginfo('stylesheet_directory') . '/lib/scripts/fancybox/jquery.fancybox-1.3.4.css');
	}
	
	function print_the_js() {
	    ?>
		<script type="text/javascript">
			jQuery(function ($) {
				var clicked = false;
				var orig_send_to_editor = window.send_to_editor;
				$('#c2_open_media<?php echo str_replace('-', '_', $this->id); ?>').click(function() {
					clicked = true;
					var url = $(this).attr('rel');
					var button = $(this);
					$.fancybox({
						href: url,
						type: 'iframe',
	        			width: 681,
	        			height: 600,
	        			onCleanup: function (){}
					});
					var input = $(this).parent('').find('input').not('#' + $(this).attr('id'));
					
					window.pb_medialibrary = function(html) {
						var data = c2_unserialize(html);
						
						if ( data.url != undefined && data.url != '' ) {
							$(input).val(data.url);
							update_img_src(input, button, data.url);
						} else {
							alert('Something went wrong... \nPlease enter the image URL manually.');
						};
						$.fancybox.close();
					}

					window.send_to_editor = ( clicked )? function(html) {
						var a = ( $('a', html).length != 0 )? $('a', html) : $('a', html).prevObject;
						imgurl = ( $('img', html).length != 0 )? $('img', html).attr('src') : $(a).attr('href');

						$(input).val(imgurl);
						update_img_src(input, button, imgurl, $('img', html).length);

						$.fancybox.close();
						clicked = false;
					} : orig_send_to_editor;
					
					if ( typeof(win) !== 'undefined' ) {
						win.send_to_editor = function(html) {
							var a = ( $('a', html).length != 0 )? $('a', html) : $('a', html).prevObject;
							imgurl = ( $('img', html).length != 0 )? $('img', html).attr('src') : $(a).attr('href');
							
							$(input).val(imgurl);
							update_img_src(input, button, imgurl, $('img', html).length);
							
							$.fancybox.close();
						}
					};
				});
				function update_img_src (input, button, src, is_img) {
					if ( typeof('is_img') != 'undefined' && is_img == 0 ) {
						return;
					};
					if ( $(input).parent().find('img.ecf-view_image').length == 0 ) {
						$(button).after('<br /><img class="ecf-view_image" src="" alt="" />');
					};
					$(input).parent().find('img.ecf-view_image').attr('src', src);
				}
			});
		</script>
		<?php
	}
}
?>