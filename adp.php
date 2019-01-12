<?php
    /**
     * This is a Class that is in charge of creating some of the Important functions for Fetching Jobs from an API
     * Includes Fetching and Creating Job Posts as Well as deleting existing Job Posts on a New Fetch
     */
    if(!class_exists('AdpJobs')):
        class AdpJobs {

            public function __construct() {
                //Nothing to do here
            }

            
            public function DeleteCustomPosts($post_type = 'post'){
                global $wpdb;
                $result = $wpdb->query( 
                    $wpdb->prepare("
                        DELETE posts,pt,pm
                        FROM wp_posts posts
                        LEFT JOIN wp_term_relationships pt ON pt.object_id = posts.ID
                        LEFT JOIN wp_postmeta pm ON pm.post_id = posts.ID
                        WHERE posts.post_type = %s
                        ", 
                        $post_type
                    ) 
                );
                return $result!==false;
            }

            /**
             * Function that Receives the Filtered Job Posts Data 
             * and Inserts it as Posts under the Adp Jobs Custom Post Type. It first Calls the Endpoint that
             * Retrieves the Bearer Token and Passes that Response to the Next Post Request that will later
             * Retrieve the Filtered Job Posts Data. ( Only Active Job Posts from the ADP API)
             */
            public function FetchAndCreateWPJobPosts(){
                $get_bearer_token = wp_remote_get( home_url() . '/wp-content/themes/cii/config/adp/adp_token_bearer.php' );
                $get_bearer_token_response = wp_remote_retrieve_body( $get_bearer_token );
                $get_bearer_token_response = json_decode($get_bearer_token_response);
                $access_token =  $get_bearer_token_response->access_token;
                $get_requisitions = wp_remote_post( 
                    home_url() . '/wp-content/themes/cii/config/adp/adp_requisitions.php',
                    array(
                        'method' => 'POST',
                        'timeout' => 45,
                        'body' => array(
                            'access_token' => $access_token
                        )
                    )           
                ); 
                $adp_jobs = wp_remote_retrieve_body( $get_requisitions );
                $adp_jobs = json_decode($adp_jobs);
               
                foreach ($adp_jobs as $adp_job) {
                  $job_title = $adp_job->job_title;
                  $job_description = $adp_job->job_description;
                  //Taking Out Unwanted HTML Attributes
                  $job_description = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $job_description);
                  //Remove Unwanted Tags
                  $tags = array("<span>", "</span>", "<font>", "</font>", "<br>");
                  $job_description =  str_replace($tags, "", $job_description);
                //   var_dump($job_description);

                  $job_type = $adp_job->job_type;
                  $job_location_name = $adp_job->job_location_name;
                  $job_location_country = $adp_job->job_location_country;
                  $job_apply_link = $adp_job->job_apply_link;
                  $job_creation_date = $adp_job->job_creation_date;
                  $job_creation_date = strtotime($job_creation_date . '12:00:00');
                  $time_diff = time() - $job_creation_date; 
                  $time_in_days = $time_diff / 3600 / 24;
                  $time_in_days = floor($time_in_days) . ' days';
                  $create_post = wp_insert_post( Array(
                    'post_content' => $job_description,
                    'post_title' => $job_title,
                    'post_type' => 'adp-jobs',
                    'post_status' => 'publish',
                    ));

                    update_field('job_type', $job_type, $create_post);
                    update_field('job_location_name', $job_location_name, $create_post);
                    update_field('job_location_country', $job_location_country, $create_post);
                    update_field('job_apply_link', $job_apply_link, $create_post);
                    // update_field('job_post_days_open', $time_in_days, $create_post); //TODO: Remove if not gonna be used in the future. 
                }
                die();
            }
        }

        /**
         * Initialize class
         */
        global $AdpJobs; $AdpJobs = new \AdpJobs();
    endif;