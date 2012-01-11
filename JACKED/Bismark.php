<?php

    class Flock extends JACKEDModule{
		const moduleName = 'Bismark';
		const moduleVersion = 1.0;
		const dependencies = 'MySQL, Sessions, Flock, Society';
		const optionalDependencies = 'Lookit';
		
		public function __construct($JACKED){
		    JACKEDModule::__construct($JACKED);
		}

		///////////////////////////

		public function getMark($id){
	        return  jackedDBGetRow(JACKED_BISMARK_DB_MARKS, "`id` = '$id'", JACKED_DEFAULT_LINK, MYSQL_ASSOC);
	    }
	    
	    //uses an $alt measurement, but if the mark was placed without one, it'll be 0 anyway, so it'll still work
	    public function getMarksWithin($lat, $long, $alt, $prox, $user = false){
	        $maxalt = $alt + $prox;
	        $minalt = $alt - $prox;
	        
	        $now = time();
	        
	        $privateClause = ($user)? "AND (M.`private` = '0' OR ((M.`owner` = F.`Person1` AND F.`Person2` = '$user') OR (M.`owner` = F.`Person2` AND F.`Person1` = '$user')) " : "AND M.`private` = '0'";
	        
	        $result = jackedDBQuery("SELECT M.*, (((ACOS(SIN($lat * PI() / 180) * SIN(latitude * PI() / 180) + COS($lat * PI() / 180) * COS(latitude * PI() / 180) * COS(($long - longitude) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) * 1609.344) AS `distance` FROM `" . JACKED_BISMARK_DB_MARKS . "` M" . ( ($user)? ", `" . JACKED_BISMARK_DB_FRIENDS . "` F" : "" ) . " WHERE (M.`altitude` BETWEEN '$minalt' AND '$maxalt' OR M.`altitude` = '0') AND M.`activeDate` <= '$now' " . $privateClause . " HAVING `distance` <= '$prox' AND `distance` <= M.`proximity` ORDER BY `distance` ASC");
	        
	        if($result){
	            $done = array();
	        
	            while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	                $done[] = $row;
	            }
	        }else
	            $done = false;
	                
	        return $done;
	    }
	    
	    public function addMark($lat, $long, $alt, $message, $activeDate, $expireDate, $proximity, $mapHidden, $private, $daysAvailable, $timeAvailableStart, $timeAvailableEnd, $Chain){
	        $active = rand(time(), time() + 259200);
	        $days = "" . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1);
	        $times = rand(1, 23);
	        $data = array(
	                      "id" => '',
	                      "message" => $message,
	                      "latitude" => $lat,
	                      "longitude" => $long,
	                      "altitude" => $alt,
	                      "owner" => rand(1, 50),
	                      "date" => time(),
	                      "activeDate" => $active,
	                      "expireDate" => rand($active, $active + 259200),
	                      "proximity" => rand(10, 200),
	                      "mapHidden" => rand(0, 1),
	                      "private" => rand(0, 1),
	                      "daysAvailable" => $days,
	                      "timeAvailableStart" => $times, 
	                      "timeAvailableEnd" => rand($times, 23),
	                      "Chain" => rand(1, 50)
	                      );
	        
	        return jackedDBInsertValues(JACKED_BISMARK_DB_MARKS, $data);
	    }
	    
	    //not for real, but for testes. ew.
	    public function testingAddMark($lat, $long, $alt, $message){
	        $active = rand(time(), time() + 259200);
	        $days = "" . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1) . rand(0, 1);
	        $times = rand(1, 23);
	        $data = array(
	                      "id" => '',
	                      "message" => $message,
	                      "latitude" => $lat,
	                      "longitude" => $long,
	                      "altitude" => $alt,
	                      "owner" => rand(1, 50),
	                      "date" => time(),
	                      "activeDate" => $active,
	                      "expireDate" => rand($active, $active + 259200),
	                      "proximity" => rand(10, 200),
	                      "mapHidden" => rand(0, 1),
	                      "private" => rand(0, 1),
	                      "daysAvailable" => $days,
	                      "timeAvailableStart" => $times, 
	                      "timeAvailableEnd" => rand($times, 23),
	                      "Chain" => rand(1, 50)
	                      );
	        
	        return jackedDBInsertValues(JACKED_BISMARK_DB_MARKS, $data);
	    }
	    
	    
	    //////////////////////////
	    //         openid auth       //
	         //////////////////////////
	    
	    public function getLoginWithOID($provider){
	        return jackedOIDGetAuthRedirect($provider, array('contact/email'), array('namePerson/friendly', 'namePerson', 'birthDate', 'person/gender', 'contact/postalCode/home', 'contact/country/home'));
	    }
	    
	    //not clean, uses $_GET directly
	    public function completeLoginWithOID(){
	        return jackedOIDHandleAuthResponse();
	    }
	    

	    //////////////////////////
	    //            CAWMMENTS      //
	         //////////////////////////
	         
	    public function getCommentsForMark($bismark, $approved = true, $howMany = 0, $page = 1, $fields = array("id", "Owner", "Bismark", "reply-to", "comment")){
	                
	        $fieldstring = getFieldString($fields, array("id", "Owner", "Bismark", "reply-to", "comment"));
	        $query = "SELECT " . $fieldstring . " FROM " . JACKED_BISMARK_DB_REPLIES . " WHERE `Bismark` = '$bismark'";
	        if($howMany != 0 && $page){
	            $query .= paginator($howMany, $page);
	        }
	        $posts = jackedDBQuery($query, JACKED_DEFAULT_LINK);
	        $done = array();
	        
	        if($posts){
	            while($row = mysql_fetch_array($posts, MYSQL_ASSOC)){
	                $done[] = $row;
	            }
	        }

	        return $done;
	    }
	        
	    public function getCommentThreadForMark($bismark){
	        $fieldstring = getFieldString($fields, array("id", "Owner", "Bismark", "reply-to", "comment"));
	        $query = "SELECT " . $fieldstring . " FROM " . JACKED_BISMARK_DB_REPLIES . " WHERE `Bismark` = '$bismark' AND `reply-to` = '0'";
	        $posts = jackedDBQuery($query, JACKED_DEFAULT_LINK);
	        $done = array();
	        
	        if($posts){
	            while($row = mysql_fetch_array($posts, MYSQL_ASSOC)){
	                $done[$row['id']] = $row;
	                $done[$row['id']]['replies'] = getReplyThreadForComment($row['id']);
	            }
	        }

	        return $done;
	    }
	    
	    public function getReplyThreadForComment($commentID){
	        $comment = jackedDBGetResult(JACKED_BISMARK_DB_REPLIES, "`reply-to` = '$commentID'", JACKED_DEFAULT_LINK, MYSQL_ASSOC);
	        $thread = array();
	        if($comment){
	            while($row = mysql_fetch_array($comment, MYSQL_ASSOC)){
	                $thread[$row['id']] = $row;
	                $thread[$row['id']]['replies'] = getReplyThreadForComment($row['id']);
	            }
	        }
	        return $thread;
	    }
	    
	    public function getCommentByID($id, $fields = array("id", "Owner", "Bismark", "reply-to", "comment")){
	                
	        $fieldstring = getFieldString($fields, array("id", "Owner", "Bismark", "reply-to", "comment"));
	        $comment = jackedDBGetRowVals($fieldstring, JACKED_BISMARK_DB_REPLIES, " `id` = '$id'", JACKED_DEFAULT_LINK, MYSQL_ASSOC);

	        return $comment;
	    }
	}
?>