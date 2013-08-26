<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * WooDojo Model Class
 *
 * The base Model for WooDojo.
 *
 * @package WordPress
 * @subpackage WooDojo
 * @category Administration
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $active_components
 * var $components
 * var $sections
 *
 * - __construct()
 * - is_active_component()
 * - is_downloaded_component()
 * - has_upgrade()
 * - get_status_token()
 * - get_status_label()
 * - get_component()
 * - get_component_slug()
 * - get_component_path()
 * - clean_component_path()
 * - load_components()
 * - load_standalone_components()
 * - load_downloadable_components()
 * - load_bundled_components()
 * - activate_component()
 * - deactivate_component()
 * - download_component()
 * - upgrade_component()
 * - get_screenshot_url()
 * - set_username()
 * - get_username()
 * - is_logged_in()
 * - get_request_error()
 */
class WooDojo_Model {
	var $active_components;
	public function __construct() {
		global $woodojo;
		
		$this->config = $woodojo->base;
		
		$this->active_components = array();
	} // End __construct()
	
	/**
	 * is_active_component function.
	 *
	 * @description Check if a specified component is active.
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return boolean $is_active
	 */
	public function is_active_component ( $component, $type ) {
		$is_active = false;

		if ( $type == 'standalone' ) {
			// Treat this as a normal plugin.
			$is_active = is_plugin_active( $this->components[$type][$component]->filepath );
		} else {
			if ( ! isset( $this->active_components[$type] ) ) {
				$this->active_components[$type] = get_option( $this->config->token . '_' . $type . '_active', array() );
			}
			
			if ( in_array( $component, array_keys( (array)$this->active_components[$type] ) ) ) {
				if ( $type == 'downloadable' && file_exists( $this->config->downloads_path . $this->components[$type][$component]->filepath ) ) {
					$is_active = true;
				} else if ( $type == 'bundled' && file_exists( $this->config->components_path . $this->components[$type][$component]->filepath ) ) {
					$is_active = true;
				} else {
					$this->deactivate_component( $component, $type, false );
				}
			}
		}
		
		return $is_active;
	} // End is_active_component()
	
	/**
	 * is_downloaded_component function.
	 *
	 * @description Check if a specified component is downloaded.
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return boolean $is_downloaded
	 */
	public function is_downloaded_component ( $component, $type ) {
		$is_downloaded = false;

		if ( $type == 'downloadable' && file_exists( $this->config->downloads_path . $this->components[$type][$component]->filepath ) ) {
			$is_downloaded = true;
		}
		
		if ( $type == 'standalone' && file_exists( trailingslashit( WP_PLUGIN_DIR ). $this->components[$type][$component]->filepath ) ) {
			$is_downloaded = true;
		}
		
		return $is_downloaded;
	} // End is_downloaded_component()

	/**
	 * has_upgrade function.
	 *
	 * @description Check if an upgrade is available for a component.
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return boolean/string $has_upgrade
	 */
	public function has_upgrade ( $component, $type ) {
		$has_upgrade = false;

		if ( ( $this->components[$type][$component]->is_free == true ) ) {
			$latest = $this->components[$type][$component]->version;
			$active = $this->get_component( $this->config->downloads_path . $this->components[$type][$component]->filepath )->version;
		}

		if ( version_compare( $active, $latest, '<' ) ) {
			$has_upgrade = $latest;
		}

		return $has_upgrade;
	} // End has_upgrade()

	/**
	 * get_status_token function.
	 * 
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return string $label
	 */
	public function get_status_token ( $component, $type ) {
		$label = 'disabled';
		
		if ( $this->is_active_component( $component, $type ) ) {
			$label = 'enabled';
		}
		
		if ( ( $type == 'downloadable' || $type == 'standalone' ) && ! $this->is_downloaded_component( $component, $type ) ) {
			$label = 'download';
		}
		
		return $label;
	} // End get_status_token()

	/**
	 * get_status_label function.
	 * 
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return string $label
	 */
	public function get_status_label ( $component, $type ) {
		$label = __( 'Disabled', 'woodojo' );
		
		if ( $this->is_active_component( $component, $type ) ) {
			$label = __( 'Enabled', 'woodojo' );
		}
		
		if ( ( $type == 'downloadable' || $type == 'standalone' ) && ! $this->is_downloaded_component( $component, $type ) ) {
			$label = __( 'Not Installed', 'woodojo' );
		}
		
		return $label;
	} // End get_status_label()
	
	/**
	 * get_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $component
	 * @return void
	 */
	public function get_component ( $component ) {
		$headers = array(
			'title' => 'Module Name',
			'short_description' => 'Module Description', 
			'version' => 'Module Version', 
			'sort' => 'Sort Order', 
			'settings' => 'Module Settings',
			'deps' => 'Dependencies'
		);
		$mod = get_file_data( $component, $headers );
		if ( empty( $mod['sort'] ) )
			$mod['sort'] = 10;
		if ( ! empty( $mod['title'] ) ) {
			$obj = new StdClass();
			
			foreach ( $mod as $k => $v ) {
				$obj->$k = $v;
			}

			if ( ! isset( $obj->product_id ) ) {
				$obj->product_id = 0;
			}

			return $obj;
		}
		return false;
	} // End get_component()
	
	/**
	 * get_component_slug function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $file
	 * @return void
	 */
	public function get_component_slug ( $file ) {
		return str_replace( '.php', '', basename( $file ) );
	} // End get_component_slug()
	
	/**
	 * get_component_path function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $slug
	 * @return void
	 */
	public function get_component_path ( $slug ) {
		return $this->config->components_path . $slug . '.php';
	} // End get_component_path()
	
	/**
	 * clean_component_path function.
	 *
	 * @description Return the component path, relative to the bundled components directory.
	 * @access public
	 * @since 1.0.0
	 * @param string $path
	 * @return string $path
	 */
	public function clean_component_path ( $path ) {
		return str_replace( $this->config->components_path, '', $path );
	} // End clean_component_path()
	
	/**
	 * load_components function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_components () {
		$this->components['bundled'] = $this->load_bundled_components();
		$this->components['downloadable'] = $this->load_downloadable_components();
		$this->components['standalone'] = $this->load_standalone_components();
	} // End load_components()
	
	/**
	 * load_standalone_components function.
	 *
	 * @description Load the standalone components.
	 * @access public
	 * @since 1.0.0
	 * @uses global $woodojo->api->get_products_by_type()
	 * @return array $components
	 */
	public function load_standalone_components () {
		global $woodojo;
		
		$response = $woodojo->api->get_products_by_type( 'standalone' );

		// Check the current version.
		foreach ( $response as $k => $v ) {
			if ( file_exists( trailingslashit( WP_PLUGIN_DIR ) . $v->filepath ) ) {
				$data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $v->filepath );
				if ( isset( $data['Version'] ) ) {
					$response[$k]->current_version = $data['Version'];
				}
			} else {
				$response[$k]->current_version = $v->version_number;
			}
		}

		return $response;
	} // End load_standalone_components()
	
	/**
	 * load_downloadable_components function.
	 *
	 * @description Load the downloadable components.
	 * @access public
	 * @since 1.0.0
	 * @uses global $woodojo->api->get_products_by_type()
	 * @return array $components
	 */
	public function load_downloadable_components () {
		global $woodojo;
		
		$response = $woodojo->api->get_products_by_type( 'downloadable' );

		// Check the current version.
		foreach ( $response as $k => $v ) {
			$response[$k]->version = $v->version_number;
			if ( file_exists( $this->config->downloads_path . $v->filepath ) ) {
				$data = $this->get_component( $this->config->downloads_path . $v->filepath );
				if ( isset( $data->version ) ) {
					$response[$k]->current_version = $data->version;
					if ( version_compare( $data->version, $v->version_number, '<' ) ) {
						$response[$k]->has_upgrade = true;
					}
				}
			}
		}

		return $response;
	} // End load_downloadable_components()
	
	/**
	 * load_bundled_components function.
	 *
	 * @description Load the components that come bundled.
	 * @access public
	 * @since 1.0.0
	 * @return array $components
	 */
	public function load_bundled_components () {
		$files = WooDojo_Utils::glob_php( '*.php', GLOB_MARK, $this->config->components_path );

		foreach ( $files as $file ) {
			if ( $headers = $this->get_component( $file ) ) {
				$slug = $this->get_component_slug( $file );
				$components[$slug] = $headers;
				$components[$slug]->filepath = $this->clean_component_path( $file );
				$components[$slug]->current_version = $components[$slug]->version;
			}
		}

		return $components;
	} // End load_bundled_components()
	
	/**
	 * activate_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @return boolean $activated
	 */
	public function activate_component ( $component, $type = 'bundled', $redirect = true ) {
		$activated = false;
		
		if ( $type == 'standalone' ) {
			activate_plugin( $this->components[$type][$component]->filepath );
			$activated = true;
		} else {
			$filepath = $this->components[$type][$component]->filepath;
			$directory = $this->config->get_directory_by_type( $type );
			
			if ( $filepath != '' && file_exists( $directory . $filepath ) ) {
				$components = get_option( $this->config->token . '_' . $type . '_active', array() );
				
				if ( ! in_array( $filepath, $components ) ) {
					$components[$component] = $filepath;
					$activated = update_option( $this->config->token . '_' . $type . '_active', $components );
				}
			}
		}
		
		if ( $redirect == true ) {
			if ( $activated == true ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&activated-component=' . $component . '&type=' . $type ) );
				exit;
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&activation-error=' . $component . '&type=' . $type ) );
				exit;
			}
		} else {
			return $activated;
		}
	} // End activate_component()
	
	/**
	 * deactivate_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @return boolean $deactivated
	 */
	public function deactivate_component ( $component, $type = 'bundled', $redirect = true ) {
		$deactivated = false;
		
		if ( $type == 'standalone' ) {
			 deactivate_plugins( array( $this->components[$type][$component]->filepath ) );
			 $deactivated = true;
		} else {
			$components = get_option( $this->config->token . '_' . $type . '_active', array() );
	
			if ( in_array( $component, array_keys( $components ) ) ) {
				unset( $components[$component] );
				$deactivated = update_option( $this->config->token . '_' . $type . '_active', $components );
			}
		}

		if ( $redirect == true ) {
			if ( $deactivated == true ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&deactivated-component=' . $component . '&type=' . $type ) );
				exit;
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&deactivation-error=' . $component . '&type=' . $type ) );
				exit;
			}
		} else {
			return $deactivated;
		}
	} // End deactivate_component()

	/**
	 * download_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @param boolean $activate
	 * @uses $woodojo->api->request_remote_file
	 * @return boolean $downloaded
	 */
	public function download_component ( $component, $type = 'bundled', $redirect = true, $activate = true ) {
		global $woodojo;

		$is_downloaded = false;

		$redirect_to = admin_url( 'admin.php?page=' . $this->config->token . '&component=' . $component . '&component-type=' . $type . '&process-action=' . 'download' . '&download-component=' . $component );

		// check_admin_referer( $component );

		// okay, let's see about getting credentials
		// $url = wp_nonce_url( 'admin.php?page=' . $this->config->token );
		if ( false === ( $creds = request_filesystem_credentials( $redirect_to, '', false, false ) ) ) {
		
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in, 
			// so stop processing for now
			
			return 'cred'; // stop the normal page form from displaying
		}
			
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $url, $method, true, false, $form_fields );
			return 'cred';
		}

		// by this point, the $wp_filesystem global should be working, so let's use it to create a file
		global $wp_filesystem;
		
		if ( $type == 'downloadable' ) {
			// Create the components directory if it doesn't exist.
			$components_dir = $this->config->downloads_path;
			if ( ! $wp_filesystem->is_dir( $components_dir ) ) {
				$wp_filesystem->mkdir( $components_dir );
			}

			// Create the backups directory if it doesn't exist.
			$backups_dir = $this->config->backups_path;

			if ( ! $wp_filesystem->is_dir( $backups_dir ) ) {
				$wp_filesystem->mkdir( $backups_dir );
			}
		}

		if ( $type == 'standalone' ) {
			$components_dir = WP_PLUGIN_DIR;
		}

		$id = $this->components[$type][$component]->product_id;

		// Download remote file.
		$file_url = $woodojo->api->request_remote_file( $id );

		if ( $file_url != '' ) {
			$remote_file = download_url( $file_url );

			// Make sure to remove the existing copy, before loading in the latest version.
			if ( ! is_wp_error( $remote_file ) ) {
				if ( $wp_filesystem->is_dir( $components_dir . $component ) ) {
					$wp_filesystem->rmdir( $components_dir . $component, true );
				}
			}

			$downloaded = unzip_file( $remote_file, $components_dir );

			if ( is_wp_error( $downloaded ) ) {
				$errors = array();

				foreach ( $downloaded->errors as $k => $v ) {
					$errors[] = '<strong>' . $v[0] . '</strong> ' . $downloaded->error_data[$k];
				}

				set_transient( $this->config->token . '-request-error', $errors );
				$is_downloaded = false;
			} else {
				$is_downloaded = true;
				if ( $activate == true ) {
					unlink( $remote_file );
					$this->activate_component( $component, $type );
				}
			}

			unlink( $remote_file );
		}

		if ( $redirect == true ) {
			if ( $is_downloaded == true ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&downloaded-component=' . $component . '&type=' . $type ) );
				exit;
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&download-error=' . $component . '&type=' . $type ) );
				exit;
			}
		} else {
			return $downloaded;
		}
	} // End download_component()

	/**
	 * upgrade_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @return void
	 */
	public function upgrade_component ( $component, $type = 'downloadable', $redirect = true ) {
		global $woodojo;

		$redirect_to = admin_url( 'admin.php?page=' . $this->config->token . '&component=' . $component . '&component-type=' . $type . '&process-action=' . 'upgrade' . '&upgrade-component=' . $component . '&activate=false' );

		// Backup the current version.
		$dir = $this->config->downloads_path;
		if ( $type == 'bundled' ) {
			$dir = $this->config->components_path;
		}

		$component_dir = $dir . dirname( $this->components[$type][$component]->filepath );

		$files = WooDojo_Utils::glob_php( '*', 0, $component_dir );

		if ( count( $files ) > 0 ) {
			foreach ( $files as $k => $v ) {
				if ( $v == $dir || is_dir( $v ) ) {
					unset( $files[$k] );
				} else {
					$files[$k] = str_replace( $this->config->downloads_path, '', $v );
				}
			}
		}

		$has_zip = WooDojo_Utils::create_zip( $files, $dir, $this->config->backups_path . sanitize_title_with_dashes( $component ) . '.zip', true );

		if ( $has_zip == true ) {
			// Download the latest version.
			$upgraded = $this->download_component( $component, $type, false, false );
		}

		if ( $upgraded == true ) {
			wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&upgraded-component=' . $component . '&type=' . $type ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&upgrade-error=' . $component . '&type=' . $type ) );
			exit;
		}
	} // End upgrade_component()
	
	/**
	 * get_screenshot_url function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	public function get_screenshot_url ( $component, $type ) {
		global $woodojo;

		$html = '';

		// Custom screenshot URL specified.
		if ( isset( $this->components[$type][$component]->thumbnail ) && ( $this->components[$type][$component]->thumbnail != '' ) ) {
			if ( stristr( $this->components[$type][$component]->thumbnail, 'http://' ) ) {
				$html = esc_url( $this->components[$type][$component]->thumbnail );
			} else {
				$html = esc_url( $woodojo->settings->screenshot_url . $this->components[$type][$component]->thumbnail );
			}
		}

		// Try and find the screenshot if no URL is specified.
		if ( $html == '' ) {
			switch ( $type ) {
				case 'standalone':
					$path = trailingslashit( WP_PLUGIN_DIR );
					$url = trailingslashit( WP_PLUGIN_URL );
				break;
				
				case 'downloadable':
					$path = $this->config->downloads_path;
					$url = $this->config->downloads_url;
				break;
				
				case 'bundled':
				default:
					$path = $this->config->components_path;
					$url = $this->config->components_url;
				break;
			}
			
			$screenshot_path = trailingslashit( $path . dirname( $this->components[$type][$component]->filepath ) );
			$screenshot_url = trailingslashit( $url . dirname( $this->components[$type][$component]->filepath ) );
			
			foreach ( array( 'png', 'jpg', 'jpeg', 'gif' ) as $k => $v ) {
				if ( file_exists( $screenshot_path . 'dojo-screenshot.' . $v ) ) {
					$html = $screenshot_url . 'dojo-screenshot.' . $v;
					
					break;
				}
			}
		}
		
		// If no screenshot, look in the "assets/screenshots" folder for component-screenshot.ext.
		if ( $html == '' ) {
			foreach ( array( 'png', 'jpg', 'jpeg', 'gif' ) as $k => $v ) {
				if ( file_exists( $this->config->assets_path . 'screenshots/' . esc_attr( $component ) . '-screenshot.' . $v ) ) {
					$html = $this->config->assets_url . 'screenshots/' . esc_attr( $component ) . '-screenshot.' . $v;
					
					break;
				}
			}
		}

		// If no screenshot, replace with a placeholder image.
		if ( $html == '' ) {
			$html = esc_url( 'http://placehold.it/100x100' );
		}

		return $html;
	} // End get_screenshot_url()

	/**
	 * get_request_error function.
	 * 
	 * @access protected
	 * @since 1.0.0
	 * @return string $message
	 */
	protected function get_request_error () {
		$notice = get_transient( $this->config->token . '-request-error' );
		$message = '';

		if ( $notice != '' && ! is_array( $notice ) ) { $message = wpautop( '<strong>' . __( 'Message:', 'woodojo' ) . '</strong> ' . $notice ); }
		if ( is_array( $notice ) && count( $notice ) > 0 ) {
			foreach ( $notice as $k => $v ) {
				$message .= wpautop( $v );
			}
		}

		return $message;
	} // End get_request_error()
} // End Class
?>