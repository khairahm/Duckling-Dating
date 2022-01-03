<?php

/**
 * Plugin Name: mobile-api
 * 
 * Uses Wordpress built in AAM function to check the JWT token which will be given during authentication process
 * 
 * returns true with valid jwt token and false with invalid token
 * 
 * NOTE: MUST BE CALLED AT THE BEGINING OF EVERY FUNCTION
 */
function validate( WP_REST_Request $request){
    $url = 'https://datingapp.wearetheducklings.com/wp-json/aam/v1/validate-jwt';
    $jwt = $request['jwt'];

    $data = ['jwt' => $jwt];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, true);

    if ($result['isValid'] == true){
        return true;
    }
    else{
        return false;
    }
}

/**
 * Gets all of the current users
 * 
 * Returns all users basic info
 */
function get_all_users( WP_REST_Request $request ){
    // Make user has proper jwt token
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    if (empty($request['gender'])){
        return "No Gender";
    }
    if (empty($request['seeking'])){
        return "No Seeking";
    }

    $gender_request = $request['gender'];
    $seeking_request = $request['seeking'];
    $start_index = intval($request['start_index']);
    $end_index = intval($request['end_index']);
    $sort_type = $request['sort'];
    $min_age = $request['min_age'];
    $max_age = $request['max_age'];

    $where = "WHERE gender = '$gender_request' AND seeking = '$seeking_request' AND age BETWEEN CAST('$max_age' AS DATE) AND CAST('$min_age' AS DATE)";

    global $wpdb;

    $all_users_info = array();

    if ($sort_type == "Random"){
        $all_users = $wpdb->get_results("SELECT * FROM `dating_dsp_user_profiles` $where ORDER BY RAND()");
    }
    else if ($sort_type == "AgeLow"){
        $all_users = $wpdb->get_results("SELECT * FROM `dating_dsp_user_profiles` $where ORDER BY age DESC");
    }
    else if ($sort_type == "AgeHigh"){
        $all_users = $wpdb->get_results("SELECT * FROM `dating_dsp_user_profiles` $where ORDER BY age ASC");
    }
    else if ($sort_type == "NewUserFirst"){
        $all_users = $wpdb->get_results("SELECT dating_dsp_user_profiles.*, dating_users.user_registered FROM `dating_dsp_user_profiles` JOIN `dating_users` ON dating_dsp_user_profiles.user_id = dating_users.ID $where ORDER BY dating_users.user_registered DESC");
    }
    else if ($sort_type == "OldUserFirst"){
        $all_users = $wpdb->get_results("SELECT dating_dsp_user_profiles.*, dating_users.user_registered FROM `dating_dsp_user_profiles` JOIN `dating_users` ON dating_dsp_user_profiles.user_id = dating_users.ID $where ORDER BY dating_users.user_registered ASC");
    }

    $last_index = count($all_users);

    if ($end_index > $last_index){ $end_index = $last_index; }
    if ($start_index > $end_index){ return true; }

    for ($i = $start_index; $i < $end_index; $i++) {
        $user = $all_users[$i];

        //Various values
        $user_id = $user->user_id;
        $registered_date = $wpdb->get_results("SELECT user_registered FROM dating_users WHERE ID = $user_id");
        $country_id = $user->country_id;
        $country = $wpdb->get_results("SELECT name FROM dating_dsp_country WHERE country_id = $country_id");
        $state_id = $user->state_id;
        $state = $wpdb->get_results("SELECT name FROM dating_dsp_state WHERE state_id = $state_id");
        $city_id = $user->city_id;
        $city = $wpdb->get_results("SELECT name FROM dating_dsp_city WHERE country_id = $city_id");
        $duck_colour = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 17 AND user_id = $user_id");
        $relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 19 AND user_id = $user_id");
        $seeking_relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 16 AND user_id = $user_id");
        $display_name = $wpdb->get_results("SELECT display_name FROM dating_users WHERE ID = $user_id");
		
		$deal_breaker=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 23 AND user_id = $user_id");
		$perfect_match=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 21 AND user_id = $user_id");
		$fav_guilty=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 25 AND user_id = $user_id");
		$elaborate_color=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 18 AND user_id = $user_id");
		$important_for=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 22 AND user_id = $user_id");
        $seeking_explination=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 20 AND user_id = $user_id");
        $engaging_quality=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 24 AND user_id = $user_id");
        $first_date=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 26 AND user_id = $user_id");
        $bucket_list=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 27 AND user_id = $user_id");
        $anything_else=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 28 AND user_id = $user_id");

        //profile picture
        $profile_picture = get_picture($user_id);

        //Duckling Colours
        $yellow = false;
        $red = false;
        $green = false;
        $white = false;
        $black = false;
        $colour_string = json_encode($duck_colour);
        if(strpos($colour_string, "Yellow (alternative)") !== false){ $yellow = true; }
        if(strpos($colour_string, "Red (do me now)") !== false){ $red = true; }
        if(strpos($colour_string, "Green (traditional)") !== false){ $green = true; }
        if(strpos($colour_string, "White (gay or bi)") !== false){ $white = true; }
        if(strpos($colour_string, "Black (kinky)") !== false){ $black = true; }

        // Filter on Colour
        $continue = true;
        $all_false = true;
        if($request['yellow'] == "True"){
            if($yellow == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['red'] == "True"){
            if($red == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['green'] == "True"){
            if($green == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['white'] == "True"){
            if($white == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['black'] == "True"){
            if($black == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($all_false){break;}

        if($continue == true){
            if($i == $last_index){
                break;
            }
            else{
                if($end_index != $last_index){
                    $end_index++;
                }
                continue;
            }
        }

        // Filter by current relationship
        $current_relationship = $relationship[0]->option_value;

        $continue = true;
        $all_false = true;
        if($request['current_single'] == "True"){
            if($current_relationship == "Single"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_dating'] == "True"){
            if($current_relationship == "Dating"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_exclusive'] == "True"){
            if($current_relationship == "Exclusive"){
                $continue = false;
            }
        }
        if($request['current_open'] == "True"){
            if($current_relationship == "Open"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_poly'] == "True"){
            if($current_relationship == "Poly"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_swinger'] == "True"){
            if($current_relationship == "Swinger"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_communal'] == "True"){
            if($current_relationship == "Communal"){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['current_hallpass'] == "True"){
            if($current_relationship == "Hall Pass"){
                $continue = false;
            }
            $all_false = false;
        }
        if($all_false){break;}

        if($continue == true){
            if($i == $last_index){
                break;
            }
            else{
                if($end_index != $last_index){
                    $end_index++;
                }
                continue;
            }
        }
        
        //Seeking relationship
        $friendship = false;
        $dating = false;
        $fling = false;
        $intimate_encounter = false;
        $short_term = false;
        $long_term = false;
        $marriage = false;
        $anything = false;
        $seeking_string = json_encode($seeking_relationship);
        if(strpos($seeking_string, "Friendship") !== false){ $friendship = true; }
        if(strpos($seeking_string, "Dating") !== false){ $dating = true; }
        if(strpos($seeking_string, "Fling") !== false){ $fling = true; }
        if(strpos($seeking_string, "Intimate Encounter") !== false){ $intimate_encounter = true; }
        if(strpos($seeking_string, "Short Term") !== false){ $short_term = true; }
        if(strpos($seeking_string, "Long Term") !== false){ $long_term = true; }
        if(strpos($seeking_string, "Marriage") !== false){ $marriage = true; }
        if(strpos($seeking_string, "Anything and Everything!") !== false){ $anything = true; }

        // Filter seeking
        $continue = true;
        $all_false = true;
        if($request['seeking_friendship'] == "True"){
            if($friendship == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_dating'] == "True"){
            if($dating == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_fling'] == "True"){
            if($fling == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_intimate'] == "True"){
            if($intimate_encounter == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_short'] == "True"){
            if($short_term == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_long'] == "True"){
            if($long_term == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_marriage'] == "True"){
            if($marriage == true){
                $continue = false;
            }
            $all_false = false;
        }
        if($request['seeking_anything'] == "True"){
            if($anything == true){
                $continue = false;
            }
            $all_false = false;
        }
        if ($all_false){break;}

        if($continue == true){
            if($i == $last_index){
                break;
            }
            else{
                if($end_index != $last_index){
                    $end_index++;
                }
                continue;
            }
        }

        $user_info['UserProfileID'] = $user->user_profile_id;
        $user_info['UserID'] = $user->user_id;
        $user_info['Country'] = $country[0]->name;
        $user_info['State'] = $state[0]->name;
        $user_info['City'] = $city[0]->name;
        $user_info['Gender'] = $user->gender;
        $user_info['SeekingGender'] = $user->seeking;
        $user_info['ZipCode'] = $user->zipcode;
        $user_info['BirthDateString'] = $user->age;
        $user_info['PicStatus'] = $user->pic_status;
        $user_info['AboutMe'] = $user->about_me;
        $user_info['MyInterests'] = $user->my_interest;
        $user_info['StatusId'] = $user->status_id;
        $user_info['Reasonforstatue'] = $user->reason_for_status;
        $user_info['Edited'] = $user->edited;
        $user_info['LastUpdateDate'] = $user->last_update_date;
        $user_info['StealthMode'] = $user->stealth_mode;
        $user_info['MakePrivate'] = $user->make_private;
        $user_info['MyStatus'] = $user->my_status;
        $user_info['FeaturedMember'] = $user->featured_member;
        $user_info['FeaturedExpirationDate'] = $user->featured_expiration_date;
        $user_info['Lattitude'] = $user->lat;
        $user_info['Logitude'] = $user->lng;
        $user_info['MakePrivateProfile'] = $user->make_private_profile;
        $user_info['AndroidDevice'] = $user->android_device;
        $user_info['IosDevice'] = $user->ios_device;
        $user_info['CurrentRelationship'] = $relationship[0]->option_value;
        $user_info['ProfilePhotoUrl'] = $profile_picture;
        $user_info['DisplayName'] = $display_name[0]->display_name;
        $user_info['DealBreaker']=$deal_breaker[0]->option_value;
        $user_info['PerfectMatch']=$perfect_match[0]->option_value;
        $user_info['FavoriteGuilty']=$fav_guilty[0]->option_value;
        $user_info['ElaborateColor']=$elaborate_color[0]->option_value;
        $user_info['ImportantFor']=$important_for[0]->option_value;
        $user_info['SeekingExplination']=$seeking_explination[0]->option_value;
        $user_info['EngaginQuality']=$engaging_quality[0]->option_value;
        $user_info['FirstDate']=$first_date[0]->option_value;
        $user_info['BucketList']=$bucket_list[0]->option_value;
        $user_info['AnythingElse']=$anything_else[0]->option_value;
        $user_info['RegisteredDateString']=$registered_date[0]->user_registered;

        //Colours
        $user_info['DuckYellow']=$yellow;
        $user_info['DuckRed']=$red;
        $user_info['DuckGreen']=$green;
        $user_info['DuckWhite']=$white;
        $user_info['DuckBlack']=$black;

        //SeekingRelationship
        $user_info['SeekingFriendship']=$friendship;
        $user_info['SeekingDating']=$dating;
        $user_info['SeekingFling']=$fling;
        $user_info['SeekingIntimateEncounter']=$intimate_encounter;
        $user_info['SeekingShortTerm']=$short_term;
        $user_info['SeekingLongTerm']=$long_term;
        $user_info['SeekingMarriage']=$marriage;
        $user_info['SeekingAnything']=$anything;
		

        array_push($all_users_info, $user_info);
    }

    $ending['end_index'] = $end_index;
    array_push($all_users_info, $ending);

    return $all_users_info;
}

/**
 * Gets all of the current users
 * 
 * Returns all users basic info
 */
function get_all_users_by_string( WP_REST_Request $request ){
    // Make user has proper jwt token
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    $search_name = $request['search_name'];
    $start_index = intval($request['start_index']);
    $end_index = intval($request['end_index']);

    global $wpdb;

    $all_users = $wpdb->get_results("SELECT * FROM `dating_dsp_user_profiles` JOIN dating_users ON dating_users.ID = user_id WHERE dating_users.display_name LIKE '%$search_name%'");

    $all_users_info = array();

    $last_index = (count($all_users));

    if ($end_index > $last_index){ $end_index = $last_index; }
    if ($start_index > $end_index){ return true; }

    for ($i = $start_index; $i < $end_index; $i++) {
        $user = $all_users[$i];

        //Various values
        $user_id = $user->user_id;
        $registered_date = $wpdb->get_results("SELECT user_registered FROM dating_users WHERE ID = $user_id");
        $country_id = $user->country_id;
        $country = $wpdb->get_results("SELECT name FROM dating_dsp_country WHERE country_id = $country_id");
        $state_id = $user->state_id;
        $state = $wpdb->get_results("SELECT name FROM dating_dsp_state WHERE state_id = $state_id");
        $city_id = $user->city_id;
        $city = $wpdb->get_results("SELECT name FROM dating_dsp_city WHERE country_id = $city_id");
        $duck_colour = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 17 AND user_id = $user_id");
        $relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 19 AND user_id = $user_id");
        $seeking_relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 16 AND user_id = $user_id");
        $display_name = $wpdb->get_results("SELECT display_name FROM dating_users WHERE ID = $user_id");
		
		$deal_breaker=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 23 AND user_id = $user_id");
		$perfect_match=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 21 AND user_id = $user_id");
		$fav_guilty=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 25 AND user_id = $user_id");
		$elaborate_color=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 18 AND user_id = $user_id");
		$important_for=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 22 AND user_id = $user_id");
        $seeking_explination=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 20 AND user_id = $user_id");
        $engaging_quality=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 24 AND user_id = $user_id");
        $first_date=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 26 AND user_id = $user_id");
        $bucket_list=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 27 AND user_id = $user_id");
        $anything_else=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 28 AND user_id = $user_id");

        //profile picture
        $profile_picture = get_picture($user_id);

        //Duckling Colours
        $yellow = false;
        $red = false;
        $green = false;
        $white = false;
        $black = false;
        $colour_string = json_encode($duck_colour);
        if(strpos($colour_string, "Yellow (alternative)") !== false){ $yellow = true; }
        if(strpos($colour_string, "Red (do me now)") !== false){ $red = true; }
        if(strpos($colour_string, "Green (traditional)") !== false){ $green = true; }
        if(strpos($colour_string, "White (gay or bi)") !== false){ $white = true; }
        if(strpos($colour_string, "Black (kinky)") !== false){ $black = true; }
        
        //Seeking relationship
        $friendship = false;
        $dating = false;
        $fling = false;
        $intimate_encounter = false;
        $short_term = false;
        $long_term = false;
        $marriage = false;
        $anything = false;
        $seeking_string = json_encode($seeking_relationship);
        if(strpos($seeking_string, "Friendship") !== false){ $friendship = true; }
        if(strpos($seeking_string, "Dating") !== false){ $dating = true; }
        if(strpos($seeking_string, "Fling") !== false){ $fling = true; }
        if(strpos($seeking_string, "Intimate Encounter") !== false){ $intimate_encounter = true; }
        if(strpos($seeking_string, "Short Term") !== false){ $short_term = true; }
        if(strpos($seeking_string, "Long Term") !== false){ $long_term = true; }
        if(strpos($seeking_string, "Marriage") !== false){ $marriage = true; }
        if(strpos($seeking_string, "Anything and Everything!") !== false){ $anything = true; }

        $user_info['UserProfileID'] = $user->user_profile_id;
        $user_info['UserID'] = $user->user_id;
        $user_info['Country'] = $country[0]->name;
        $user_info['State'] = $state[0]->name;
        $user_info['City'] = $city[0]->name;
        $user_info['Gender'] = $user->gender;
        $user_info['SeekingGender'] = $user->seeking;
        $user_info['ZipCode'] = $user->zipcode;
        $user_info['BirthDateString'] = $user->age;
        $user_info['PicStatus'] = $user->pic_status;
        $user_info['AboutMe'] = $user->about_me;
        $user_info['MyInterests'] = $user->my_interest;
        $user_info['StatusId'] = $user->status_id;
        $user_info['Reasonforstatue'] = $user->reason_for_status;
        $user_info['Edited'] = $user->edited;
        $user_info['LastUpdateDate'] = $user->last_update_date;
        $user_info['StealthMode'] = $user->stealth_mode;
        $user_info['MakePrivate'] = $user->make_private;
        $user_info['MyStatus'] = $user->my_status;
        $user_info['FeaturedMember'] = $user->featured_member;
        $user_info['FeaturedExpirationDate'] = $user->featured_expiration_date;
        $user_info['Lattitude'] = $user->lat;
        $user_info['Logitude'] = $user->lng;
        $user_info['MakePrivateProfile'] = $user->make_private_profile;
        $user_info['AndroidDevice'] = $user->android_device;
        $user_info['IosDevice'] = $user->ios_device;
        $user_info['CurrentRelationship'] = $relationship[0]->option_value;
        $user_info['ProfilePhotoUrl'] = $profile_picture;
        $user_info['DisplayName'] = $display_name[0]->display_name;
        $user_info['DealBreaker']=$deal_breaker[0]->option_value;
        $user_info['PerfectMatch']=$perfect_match[0]->option_value;
        $user_info['FavoriteGuilty']=$fav_guilty[0]->option_value;
        $user_info['ElaborateColor']=$elaborate_color[0]->option_value;
        $user_info['ImportantFor']=$important_for[0]->option_value;
        $user_info['SeekingExplination']=$seeking_explination[0]->option_value;
        $user_info['EngaginQuality']=$engaging_quality[0]->option_value;
        $user_info['FirstDate']=$first_date[0]->option_value;
        $user_info['BucketList']=$bucket_list[0]->option_value;
        $user_info['AnythingElse']=$anything_else[0]->option_value;
        $user_info['RegisteredDateString']=$registered_date[0]->user_registered;

        //Colours
        $user_info['DuckYellow']=$yellow;
        $user_info['DuckRed']=$red;
        $user_info['DuckGreen']=$green;
        $user_info['DuckWhite']=$white;
        $user_info['DuckBlack']=$black;

        //SeekingRelationship
        $user_info['SeekingFriendship']=$friendship;
        $user_info['SeekingDating']=$dating;
        $user_info['SeekingFling']=$fling;
        $user_info['SeekingIntimateEncounter']=$intimate_encounter;
        $user_info['SeekingShortTerm']=$short_term;
        $user_info['SeekingLongTerm']=$long_term;
        $user_info['SeekingMarriage']=$marriage;
        $user_info['SeekingAnything']=$anything;
		
        array_push($all_users_info, $user_info);
    }

    $ending['end_index'] = $end_index;
    array_push($all_users_info, $ending);

    return $all_users_info;
}


/**
 * Gets a single user's info
 * 
 * gets all info about a single user
 */

function get_user_info( WP_REST_Request $request ) {
    // Make user has proper jwt token
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    global $wpdb;

    $user_id = $request['id'];

    $user = $wpdb->get_results("SELECT * FROM dating_dsp_user_profiles WHERE user_id = $user_id");

    //Various values
    $country_id = $user[0]->country_id;
    $country = $wpdb->get_results("SELECT name FROM dating_dsp_country WHERE country_id = $country_id");
    $state_id = $user[0]->state_id;
    $state = $wpdb->get_results("SELECT name FROM dating_dsp_state WHERE state_id = $state_id");
    $city_id = $user[0]->city_id;
    $city = $wpdb->get_results("SELECT name FROM dating_dsp_city WHERE country_id = $city_id");
    $duck_colour = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 17 AND user_id = $user_id");
    $relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 19 AND user_id = $user_id");
    $seeking_relationship = $wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 16 AND user_id = $user_id");
    $display_name = $wpdb->get_results("SELECT display_name FROM dating_users WHERE ID = $user_id");
    
    $deal_breaker=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 23 AND user_id = $user_id");
    $perfect_match=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 21 AND user_id = $user_id");
    $fav_guilty=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 25 AND user_id = $user_id");
    $elaborate_color=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 18 AND user_id = $user_id");
    $important_for=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 22 AND user_id = $user_id");
    $seeking_explination=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 20 AND user_id = $user_id");
    $engaging_quality=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 24 AND user_id = $user_id");
    $first_date=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 26 AND user_id = $user_id");
    $bucket_list=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 27 AND user_id = $user_id");
    $anything_else=$wpdb->get_results("SELECT option_value FROM dating_dsp_profile_questions_details WHERE profile_question_id = 28 AND user_id = $user_id");

    //profile picture
    $profile_picture = get_picture($user_id);

    //Duckling Colours
    $yellow = false;
    $red = false;
    $green = false;
    $white = false;
    $black = false;
    $colour_string = json_encode($duck_colour);
    if(strpos($colour_string, "Yellow (alternative)") !== false){ $yellow = true; }
    if(strpos($colour_string, "Red (do me now)") !== false){ $red = true; }
    if(strpos($colour_string, "Green (traditional)") !== false){ $green = true; }
    if(strpos($colour_string, "White (gay or bi)") !== false){ $white = true; }
    if(strpos($colour_string, "Black (kinky)") !== false){ $black = true; }

    //Seeking relationship
    $friendship = false;
    $dating = false;
    $fling = false;
    $intimate_encounter = false;
    $short_term = false;
    $long_term = false;
    $marriage = false;
    $anything = false;
    $seeking_string = json_encode($seeking_relationship);
    if(strpos($seeking_string, "Friendship") !== false){ $friendship = true; }
    if(strpos($seeking_string, "Dating") !== false){ $dating = true; }
    if(strpos($seeking_string, "Fling") !== false){ $fling = true; }
    if(strpos($seeking_string, "Intimate Encounter") !== false){ $intimate_encounter = true; }
    if(strpos($seeking_string, "Short Term") !== false){ $short_term = true; }
    if(strpos($seeking_string, "Long Term") !== false){ $long_term = true; }
    if(strpos($seeking_string, "Marriage") !== false){ $marriage = true; }
    if(strpos($seeking_string, "Anything and Everything!") !== false){ $anything = true; }

    $user_info['UserProfileID'] = $user[0]->user_profile_id;
    $user_info['UserID'] = $user[0]->user_id;
    $user_info['Country'] = $country[0]->name;
    $user_info['State'] = $state[0]->name;
    $user_info['City'] = $city[0]->name;
    $user_info['Gender'] = $user[0]->gender;
    $user_info['SeekingGender'] = $user[0]->seeking;
    $user_info['ZipCode'] = $user[0]->zipcode;
    $user_info['BirthDateString'] = $user[0]->age;
    $user_info['PicStatus'] = $user[0]->pic_status;
    $user_info['AboutMe'] = $user[0]->about_me;
    $user_info['MyInterests'] = $user[0]->my_interest;
    $user_info['StatusId'] = $user[0]->status_id;
    $user_info['Reasonforstatue'] = $user[0]->reason_for_status;
    $user_info['Edited'] = $user[0]->edited;
    $user_info['LastUpdateDate'] = $user[0]->last_update_date;
    $user_info['StealthMode'] = $user[0]->stealth_mode;
    $user_info['MakePrivate'] = $user[0]->make_private;
    $user_info['MyStatus'] = $user[0]->my_status;
    $user_info['FeaturedMember'] = $user[0]->featured_member;
    $user_info['FeaturedExpirationDate'] = $user[0]->featured_expiration_date;
    $user_info['Lattitude'] = $user[0]->lat;
    $user_info['Logitude'] = $user[0]->lng;
    $user_info['MakePrivateProfile'] = $user[0]->make_private_profile;
    $user_info['AndroidDevice'] = $user[0]->android_device;
    $user_info['IosDevice'] = $user[0]->ios_device;
    $user_info['CurrentRelationship'] = $relationship[0]->option_value;
    $user_info['ProfilePhotoUrl'] = $profile_picture;
    $user_info['DisplayName'] = $display_name[0]->display_name;
    $user_info['DealBreaker']=$deal_breaker[0]->option_value;
    $user_info['PerfectMatch']=$perfect_match[0]->option_value;
    $user_info['FavoriteGuilty']=$fav_guilty[0]->option_value;
    $user_info['ElaborateColor']=$elaborate_color[0]->option_value;
    $user_info['ImportantFor']=$important_for[0]->option_value;
    $user_info['SeekingExplination']=$seeking_explination[0]->option_value;
    $user_info['EngaginQuality']=$engaging_quality[0]->option_value;
    $user_info['FirstDate']=$first_date[0]->option_value;
    $user_info['BucketList']=$bucket_list[0]->option_value;
    $user_info['AnythingElse']=$anything_else[0]->option_value;

    //Colours
    $user_info['DuckYellow']=$yellow;
    $user_info['DuckRed']=$red;
    $user_info['DuckGreen']=$green;
    $user_info['DuckWhite']=$white;
    $user_info['DuckBlack']=$black;

    //SeekingRelationship
    $user_info['SeekingFriendship']=$friendship;
    $user_info['SeekingDating']=$dating;
    $user_info['SeekingFling']=$fling;
    $user_info['SeekingIntimateEncounter']=$intimate_encounter;
    $user_info['SeekingShortTerm']=$short_term;
    $user_info['SeekingLongTerm']=$long_term;
    $user_info['SeekingMarriage']=$marriage;
    $user_info['SeekingAnything']=$anything;

	return $user_info;	
}

function get_messages( WP_REST_Request $request ){
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    global $wpdb;

    $thread_id = $request['thread_id'];
    $start_index = intval($request['start_index']);
    $end_index = intval($request['end_index']);

    $messages = $wpdb->get_results("SELECT * FROM `dating_dsp_messages` WHERE `thread_id` = $thread_id ORDER BY `sent_date` DESC");

    $all_messages = [];

    if ($end_index > count($messages)){ $end_index = count($messages); }
    if ($start_index > $end_index){ return true; }

    for ($i = $end_index; $i >= $start_index; $i--) {
        
        if($messages[$i] !== null){
            array_push($all_messages, $messages[$i]);
        }
    }

    return $all_messages;
}

function get_chat_rooms( WP_REST_Request $request ){
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    global $wpdb;

    $all_rooms = array();

    $user_id = $request['id'];
    $chatRooms = $wpdb->get_results("SELECT * FROM `dating_dsp_messages` WHERE (`sender_id` = $user_id OR `receiver_id` = $user_id) AND `sent_date` IN(SELECT MAX(`sent_date`) FROM `dating_dsp_messages` GROUP BY `thread_id`)");

    foreach($chatRooms as $msg){
        
        if($msg->sender_id == $user_id){
            $other_user = $wpdb->get_results("SELECT * FROM dating_dsp_user_profiles WHERE user_id = $msg->receiver_id");
        }
        else{
            $other_user = $wpdb->get_results("SELECT * FROM dating_dsp_user_profiles WHERE user_id = $msg->sender_id");
        }

        $other_user_id = $other_user[0]->user_id;

        $display_name = $wpdb->get_results("SELECT display_name FROM dating_users WHERE ID = $other_user_id");
        
        $chatRoom['id'] = $msg->thread_id;
        $chatRoom['UserId'] = $other_user_id;
        $chatRoom['CurrentUserId'] = $user_id;
        $chatRoom['ChatName'] = $display_name[0]->display_name;
        $chatRoom['LastMsg'] = $msg->text_message;
        $chatRoom['PhotoUrl'] = get_picture($other_user_id);

        array_push($all_rooms, $chatRoom);
    }
    return $all_rooms;
}

function send_message( WP_REST_Request $request ){
    
    $valid = validate($request);
    if (!$valid){
        return false;
    }

    $sender_id = $request['sender_id'];
    $receiver_id = $request['receiver_id'];
    $sent_date = $request['sent_date'];
    $text_message = $request['text_message'];
    $thread_id = $request['thread_id'];

    global $wpdb;

    $wpdb->get_results("INSERT INTO `dating_dsp_messages`(`sender_id`, `receiver_id`, `sent_date`, `text_message`, `message_read`, `thread_id`) VALUES ($sender_id, $receiver_id, '$sent_date', '$text_message', 'N', $thread_id)");

}

function get_picture($user_id){
    $profile_picture = false;
    $dir = "./wp-content/uploads/dsp_media/user_photos/user_{$user_id}/";
    $dir = scandir($dir);
    if($dir != false){
        $image_name = "";
        $found = false;

        foreach($dir as $value){
            if((strpos($value, ".jpeg") !== false) || (strpos($value, ".jpg") !== false) || (strpos($value, ".png") !== false)){
                $image_name = $value;
                $found = true;
            }
        }
        if($found){
            $profile_picture = "https://datingapp.wearetheducklings.com/wp-content/uploads/dsp_media/user_photos/user_{$user_id}/{$image_name}";
        }
    }
    return $profile_picture;
}

//update user profile questions
function update_user_info(WP_REST_Request $request){
       global $wpdb;
    
    $valid = validate($request);
    if (!$valid){
        
        return false;
    }
    $aboutMe=$request['AboutMe'];
    $elaborateColor=$request['ElaborateColor'];
    $seekingExplination=$request['SeekingExplination'];
    $perfectMatch=$request['PerfectMatch'];
    $importantFor=$request['ImportantFor'];
    $dealBreaker=$request['DealBreaker'];
    $favoriteGuilty=$request['FavoriteGuilty'];
    $bucketList=$request['BucketList'];
    $anythingElse=$request['AnythingElse'];
    $engaginQuality=$request['EngaginQuality'];
    $firstDate=$request['FirstDate'];
    $myInterests=$request['MyInterests'];
    $toBeUpdate['AboutMe']=$aboutMe;
    $toBeUpdate['ElaborateColor']=$elaborateColor;
    $toBeUpdate['SeekingExplination']=$seekingExplination;
    $toBeUpdate["PerfectMatch"] = $perfectMatch;
    $toBeUpdate["ImportantFor"] = $importantFor;
    $toBeUpdate["DealBreaker"] =$dealBreaker;
    $toBeUpdate["FavoriteGuilty"] =$favoriteGuilty;
    $toBeUpdate["BucketList"] = $bucketList;
    $toBeUpdate["AnythingElse"] = $anythingElse;
    $toBeUpdate["EngaginQuality"] = $engaginQuality;
    $toBeUpdate["FirstDate"] =$firstDate;
    $toBeUpdate["MyInterests"] = $myInterests;
    $toBeUpdate["user_id"]=$request['id'];
    $updateMatch="UPDATE dating_dsp_profile_questions_details set option_value = '".$perfectMatch."' WHERE user_id = ".$request['id']." AND profile_question_id = 21";
    $checkMatch="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 21 AND user_id = ".$request['id'];
	$executeMatch=$wpdb->get_results($checkMatch);
	if($executeMatch==null){
		$insertMatch="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 21, 0, '".$perfectMatch."')";
		$wpdb->get_results($insertMatch);
		
	}else{
		$wpdb->get_results($updateMatch);
		
	}
	
	$updateDeal="UPDATE dating_dsp_profile_questions_details set option_value = '".$dealBreaker."' WHERE user_id = ".$request['id']." AND profile_question_id = 23";
    $checkUpdateDeal="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 23 AND user_id = ".$request['id'];
	$executeUpdateDeal=$wpdb->get_results($checkUpdateDeal);
	if($executeUpdateDeal==null){
		$insertUpdateDeal="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 23, 0, '".$dealBreaker."')";
		$wpdb->get_results($insertUpdateDeal);
		
	}else{
		$wpdb->get_results($updateDeal);
	}
	$updateGuilty="UPDATE dating_dsp_profile_questions_details set option_value = '".$favoriteGuilty."' WHERE user_id = ".$request['id']." AND profile_question_id = 25";
    $checkGuilty="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 25 AND user_id = ".$request['id'];
	$executeGuilty=$wpdb->get_results($checkGuilty);
	if($executeGuilty==null){
		$insertGuilty="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 25, 0, '".$favoriteGuilty."')";
		$wpdb->get_results($insertGuilty);
		
	}else{
		$wpdb->get_results($updateGuilty);
	}
	
    $updateEloborateColour="UPDATE dating_dsp_profile_questions_details set option_value = '".$elaborateColor."' WHERE user_id = ".$request['id']." AND profile_question_id = 18";
    $checkElaborateColour="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 18 AND user_id = ".$request['id'];
	$executeElaborateColour=$wpdb->get_results($checkElaborateColour);
	if($executeElaborateColour==null){
		$insertElaborateColour="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 18, 0, '".$elaborateColor."')";
		$wpdb->get_results($insertElaborateColour);
		
	}else{
		$wpdb->get_results($updateEloborateColour);
	}
	
    $updateImportant="UPDATE dating_dsp_profile_questions_details set option_value = '".$importantFor."' WHERE user_id = ".$request['id']." AND profile_question_id = 22";
    $checkImportant="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 22 AND user_id = ".$request['id'];
	$executeImportant=$wpdb->get_results($checkImportant);
	if($executeImportant==null){
		$insertImportant="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 22, 0, '".$importantFor."')";
		$wpdb->get_results($insertImportant);
		
	}else{
		$wpdb->get_results($updateImportant);
	}
	
    $updateExplination="UPDATE dating_dsp_profile_questions_details set option_value = '".$seekingExplination."' WHERE user_id = ".$request['id']." AND profile_question_id = 20";
    $checkExplination="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 20 AND user_id = ".$request['id'];
	$executeExplination=$wpdb->get_results($checkExplination);
	if($executeExplination==null){
		$insertExplination="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 20, 0, '".$seekingExplination."')";
		$wpdb->get_results($insertExplination);
		
	}else{
		$wpdb->get_results($updateExplination);
	}
	
    $updateEngagin="UPDATE dating_dsp_profile_questions_details set option_value = '".$engaginQuality."' WHERE user_id = ".$request['id']." AND profile_question_id = 24";
    $checkEngain="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 24 AND user_id = ".$request['id'];
	$executeEngain=$wpdb->get_results($checkEngain);
	if($executeEngain==null){
		$insertEngain="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 24, 0, '".$engaginQuality."')";
		$wpdb->get_results($insertEngain);
		
	}else{
		$wpdb->get_results($updateEngagin);
	}
	
    $updateFirstDate="UPDATE dating_dsp_profile_questions_details set option_value = '".$firstDate."' WHERE user_id = ".$request['id']." AND profile_question_id = 26";
    $checkFirstDate="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 26 AND user_id = ".$request['id'];
	$executeFirstDate=$wpdb->get_results($checkFirstDate);
	if($executeFirstDate==null){
		$insertFirstDate="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 26, 0, '".$firstDate."')";
		$wpdb->get_results($insertFirstDate);
		
	}else{
		$wpdb->get_results($updateFirstDate);
	}
	
    $updateBuckList="UPDATE dating_dsp_profile_questions_details set option_value = '".$bucketList."' WHERE user_id = ".$request['id']." AND profile_question_id = 27";
    $checkBuckList="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 27 AND user_id = ".$request['id'];
	$executeBuckList=$wpdb->get_results($checkBuckList);
	if($executeBuckList==null){
		$insertBuckList="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 27, 0, '".$bucketList."')";
		$wpdb->get_results($insertBuckList);
		
	}else{
		$wpdb->get_results($updateBuckList);
	}
	
	
    $updateAnythingElse="UPDATE dating_dsp_profile_questions_details set option_value = '".$anythingElse."' WHERE user_id = ".$request['id']." AND profile_question_id = 28";
    $checkAnythingElse="SELECT * FROM dating_dsp_profile_questions_details WHERE profile_question_id = 28 AND user_id = ".$request['id'];
	$executeAnythingElse=$wpdb->get_results($checkAnythingElse);
	if($executeAnythingElse==null){
		$insertAnythingElse="INSERT INTO dating_dsp_profile_questions_details(user_id, profile_question_id, profile_question_option_id, option_value) VALUES (".$request['id'].", 28, 0, '".$anythingElse."')";
		$wpdb->get_results($insertAnythingElse);
		
	}else{
		$wpdb->get_results($updateAnythingElse);
	}
	
	$updateMyInterests="UPDATE dating_dsp_user_profiles set my_interest = '".$myInterests."' WHERE user_id = ".$request['id'];
	$wpdb->get_results($updateMyInterests);
	$updateAboutMe="UPDATE dating_dsp_user_profiles set about_me = '".$aboutMe."' WHERE user_id = ".$request['id'];
	$wpdb->get_results($updateAboutMe);
	
    return $toBeUpdate;
}

// Add function to the routes register
add_action('rest_api_init', function(){

    register_rest_route(
        'mobile/v1', 
        'allusers',
        [
            'methods' => 'POST',
            'callback' => 'get_all_users',
            'permission_callback' => '__return_true',
        ]
    );
    register_rest_route(
        'mobile/v1', 
        'userinfo',
        [
            'methods' => 'POST',
            'callback' => 'get_user_info',
            'permission_callback' => '__return_true',
        ]
    );
    register_rest_route(
        'mobile/v1', 
        'messages',
        [
            'methods' => 'POST',
            'callback' => 'get_messages',
            'permission_callback' => '__return_true',
        ]
    );
    register_rest_route(
        'mobile/v1', 
        'sendmsg',
        [
            'methods' => 'POST',
            'callback' => 'send_message',
            'permission_callback' => '__return_true',
        ]
    );
    register_rest_route(
		'mobile/v1', 
		'updateuser',
		[
		'methods' => 'POST',
        'callback' => 'update_user_info',
        'permission_callback' => '__return_true',
		]
	);
    register_rest_route(
		'mobile/v1', 
		'getchatrooms',
		[
		'methods' => 'POST',
        'callback' => 'get_chat_rooms',
        'permission_callback' => '__return_true',
		]
	);
    register_rest_route(
		'mobile/v1', 
		'allusersbyname',
		[
		'methods' => 'POST',
        'callback' => 'get_all_users_by_string',
        'permission_callback' => '__return_true',
		]
	);
});
