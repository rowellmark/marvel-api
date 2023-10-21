<?php

namespace MARVELAPI\App;

class App
{
  /**
   * App constructor.
   *
   * @param $file
   */
  public function __construct($file)
  {
    
    // Plugin install and uninstall process
    register_activation_hook($file, [$this, 'install']);
    register_deactivation_hook($file, [$this, 'uninstall']);


    add_action('admin_menu', [$this, 'page']);
    add_action('admin_enqueue_scripts', [$this, 'assets']);
    add_action('admin_init', [$this, 'marvel_api_register_settings']);


    add_shortcode('marvel_api', [$this, 'marvel_api_shortcode']);

  }

  /**
   * Enqueue Assets to specific page
   */
  public function assets()
  {
    if (strpos(get_current_screen()->id, MARVEL_SLUG) !== false) {
      wp_enqueue_media();
      wp_enqueue_style(MARVEL_SLUG, MARVEL_RESOURCES . 'css/app.min.css', [], time());
      wp_enqueue_script(MARVEL_SLUG, MARVEL_RESOURCES . 'js/app.min.js', [], time(), true);
      wp_localize_script(MARVEL_SLUG, 'data', [
        'nonce' => wp_create_nonce('wp_rest'),
        'baseUrl' => get_home_url()
      ]);
    }
  }

  /**
   * Register Public Keys
   */
  function marvel_api_register_settings() {
      register_setting('marvel_api_settings', 'marvel_api_public_key');
      register_setting('marvel_api_settings', 'marvel_api_private_key');
  }

  /**
   * Register admin page
   */
  public function page()
  {
    add_menu_page(
      MARVEL_NAME,
      MARVEL_NAME,
      'manage_options',
      MARVEL_SLUG,
      [$this, 'render'],
      MARVEL_RESOURCES . '/images/logo.png',
      3
    );
  }

  /**
   * Render Page
   */
  public function render()
  {
    include_once MARVEL_VIEWS . 'index.php';
  }
  
  /**
   * Plugin Installation.
   *
   * @since 1.0.0
   */
  public function install()
  {
    // Installation Process
  }

  /**
   * Plugin Uninstalling.
   *
   * @since 1.0.0
   */
  public function uninstall()
  {
    // Uninstall Process
  }

  /**
   * MARVERL API SHORTCODE
  */
  function marvel_api_shortcode($atts, $content = null) {

    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'endpoint' => '', // API endpoint (e.g., characters)
            'limit' => -1,    // Default to display all results, -1 for no limit
            'character_id' => '', // Character ID
            'comics' => false,     // Include comics by default
            'startYear' => '',  // Default start year
        ),
        $atts,
        'marvel_api'    
    );

    // Your Marvel API public and private keys
    $public_key = get_option('marvel_api_public_key');
    $private_key = get_option('marvel_api_private_key');

    // Sanitize and get the API endpoint from the shortcode
    $endpoint = sanitize_text_field($atts['endpoint']);

    // Generate a timestamp for the request
    $timestamp = time();

    // Generate a hash for the request
    $hash = md5($timestamp . $private_key . $public_key);

   $character_id = sanitize_text_field($atts['character_id']);
    $include_comics = $atts['comics'] ? '/comics' : '';
    $character_part = !empty($character_id) ? "/$character_id" : '';

    $startYear = !empty($atts['startYear']) ? '&startYear='.$atts['startYear'].'' : '';
    // Build the API URL
    $api_url = "https://gateway.marvel.com/v1/public/$endpoint$character_part$include_comics";
    $api_url .= "?ts=$timestamp&apikey=$public_key&hash=$hash".$startYear."";

    // Make the API request
    $response = wp_remote_get($api_url);
    if (is_array($response) && !is_wp_error($response)) {
        $data = json_decode($response['body']);

        // Initialize the output
        $output = '';

        // Check if data is available
        if (isset($data->data) && !empty($data->data->results)) {
            // Process the loop content within [loop_start] and [loop_end] tags
            $loop_content = preg_replace_callback(
                '/\[loop_start\](.*?)\[loop_end\]/s',
                function ($matches) use ($data, $atts) {
                    $content = '';
                    $results = $data->data->results;

                    if ($atts['limit'] > 0) {
                        $results = array_slice($results, 0, $atts['limit']);
                    }

                    foreach ($results as $result) {

                        $thumbnail_url = $result->thumbnail->path . '/standard_xlarge.' . $result->thumbnail->extension;
                        $date = date("Y", strtotime($result->modified)); 
                        $newDate = date("d M, Y", strtotime($result->dates[1]->date));
                        $comics = '';
                        $stories = '';
                        $series = '';


                        if(isset($result->comics->items)){
                            foreach($result->comics->items as $item){
                                $comics .=  '<li>'.strval($item->name).'</li>';
                            }
                        }

                        if(isset($result->stories->items)){
                            foreach($result->stories->items as $item){
                                $stories .=  '<li>'.strval($item->name).'</li>';
                            }
                        }

                        if(isset($result->series->items)){
                            foreach($result->series->items as $item){
                                $series .=  '<li>'.strval($item->name).'</li>';
                            }
                        }

                        $patterns = array(
                            '/\[date\]/',
                            '/\[author\]/',
                            '/\[name\]/',
                            '/\[title\]/',
                            '/\[description\]/',
                            '/\[year\]/',
                            '/\[real_name\]/',
                            '/\[thumbnail\]/',
                            '/\[comics\]/',
                            '/\[stories\]/',
                            '/\[series\]/',
                            
                            
                        );

                        $replacements = array(
                            esc_html($newDate),
                            esc_html($result->creators->items[0]->name),
                            esc_attr($result->name),
                            esc_attr($result->title),
                            esc_html($result->description),
                            esc_html($date),
                            esc_html($result->real_name),
                            '<img src="'.$thumbnail_url.'" alt="'.$result->name.'">',
                            $comics,
                            $stories,
                        
                        );

                        $replaced_content = preg_replace($patterns, $replacements, $matches[1]);
                                
                        $content .= do_shortcode($replaced_content); // Process nested shortcodes here


                    }
                    return $content;
                },
                $content
            );

            $output .= $loop_content;
        } else {
            // No data found
            $output = 'No data found.';
        }
    } else {
        // Error fetching data from the Marvel API
        $output = 'Error fetching data from the Marvel API.';
    }

    return $output;
  }
}
