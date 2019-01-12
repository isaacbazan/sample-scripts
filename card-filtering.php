<?php
    /**
     * This file is in charge of the Accessing WordPress DB and Querying it based on User input.
     * It's used for some of the Projects Modules
     */
    if(!class_exists('CardFilter')):
        class CardFilter {

            public function __construct() {
                //Nothing to do here
            }

            public function GetPosts($args){

                $result = [];
                $post_args = [
                    'post_type' => 'post',
                    'posts_per_page' => $args['posts_per_page'],  
                ];

                if(!$args['is_manual']){
                    //Check if only author_id is selected
                    if($args['author_id'] && !$args['tag_id'] && !$args['program_id'] && !$args['issue_id'] && !$args['category_id'] ){
                        $post_args[] = [
                            'post_type' => 'post',
                            'posts_per_page' => $args['posts_per_page'],
                            'author__in' => $args['author_id'],
                        ];
                    }else{
                        $post_args['tax_query'] = ['relation' => 'AND'];
                        if($args['program_id']){
                            $post_args[] = [
                                'author__in' => $args['author_id'],
                            ];
                            $post_args['tax_query'] = [
                                "program_clause" => [
                                    'taxonomy' => 'program',
                                    'terms'    => $args['program_id'],
                                ],
                            ];
                        }
                        if($args['tag_id']){
                            $post_args[] = [
                                'author__in' => $args['author_id'],
                            ];
                            $post_args['tax_query']['tag_clause'] = [
                                'taxonomy' => 'post_tag',
                                'terms'    => $args['tag_id'],
                            ];
                        }
                        if($args['issue_id']){
                            $post_args[] = [
                                'author__in' => $args['author_id'],
                            ];
                            $post_args['tax_query']['issue_clause'] = [
                                'taxonomy' => 'issue',
                                'terms'    => $args['issue_id'],
                            ];
                        }
                        if($args['category_id']){
                            $post_args[] = [
                                'author__in' => $args['author_id'],
                            ];
                            $post_args['tax_query']['category_clause'] = [
                                'taxonomy' => 'category',
                                'terms'    => $args['category_id'],
                            ];
                        }
                        if($args['grantee_name']){
                            $post_args['tax_query']['grantee_name_clause'] = [
                                'taxonomy'  => 'grantee_name',
                                'field'     => 'name',
                                'terms'     => $args['grantee_name']
                            ];
                        }
                    }
                }
                else{
                     $manual = [];
                    foreach($args['manual_posts'] as $post){
                        $manual[] = $post[0];
                    }
                    return $manual;
                }
                
                //POST QUERY
                $posts_query = new WP_Query($post_args);
                wp_reset_postdata();

                $result = $posts_query->posts;
                
                return $result;
            }

            public function GetProgramAcronym(int $postID){
                $post_terms = get_the_terms( $postID,  'program' );
                $post_program_slug = $post_terms[0]->slug;
                $card_tag = null;
                    switch ($post_program_slug) {
                        case 'piper-fund':
                            $card_tag = 'Piper Fund';
                            break;
                        case 'piper-action-fund':
                            $card_tag = 'Piper A Fund';
                            break;
                        case 'src':
                            $card_tag = 'src';
                            break;
                        case 'rfdc':
                            $card_tag = 'rfdc';
                            break;
                        default:
                            $card_tag = null;
                            break;
                } 
                return $card_tag;
            }

            public function GetCellSize(int $postCount){
                switch ($postCount) {
                    case 1:
                        $cell_size = 'large-12';
                        break;
                    case 2:
                        $cell_size = 'large-6';
                        break;
                    default:
                        $cell_size = 'large-4';
                        break;
                }
                return $cell_size;
            }
        }

        /**
         * Initialize class
         */
        global $CardFilter; $CardFilter = new \CardFilter();
    endif;