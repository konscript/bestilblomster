<?php
if(basename($_SERVER['SCRIPT_FILENAME']) == "optimize.class.php"):
    exit;
endif;
class IWP_MMB_Optimize extends IWP_MMB_Core
{
    function __construct()
    {
        parent::__construct();
    }
    
	function cleanup_system($cleanupType){
		
		if (isset($cleanupType["clean-revisions"])) {
		$text .= self::cleanup_type_process('revisions');
		}
	
		if (isset($cleanupType["clean-autodraft"])) {
			$text .= self::cleanup_type_process('autodraft');
			}	
			
		if (isset($cleanupType["clean-comments"])) {
			$text .= self::cleanup_type_process('spam');
			}
		
		if (isset($cleanupType["unapproved-comments"])) {
			$text .= self::cleanup_type_process('unapproved');
			}
		
		if (isset($cleanupType["optimize-db"])) {
			$text .= self::cleanup_type_process('optimize-db');
			//$text .= DB_NAME.__(" Database Optimized!<br>", 'wp-optimize');
			}
	
		if ($text !==''){
			return $text;
		}
	}
	
	function cleanup_type_process($cleanupType){
		global $wpdb;
		$clean = ""; $message = "";
		$optimized = array();
	
		switch ($cleanupType) {
			
			case "revisions":
				$clean = "DELETE FROM $wpdb->posts WHERE post_type = 'revision'";
				$revisions = $wpdb->query( $clean );
				$message .= $revisions.__(' post revisions deleted<br>', 'wp-optimize');
				
				break;
				
	
			case "autodraft":
				$clean = "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'";
				$autodraft = $wpdb->query( $clean );
				$message .= $autodraft.__(' auto drafts deleted<br>', 'wp-optimize');
				
				break;
	
			case "spam":
				$clean = "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam';";
				$comments = $wpdb->query( $clean );
				$message .= $comments.__(' spam comments deleted<br>', 'wp-optimize');
				break;
	
			case "unapproved":
				$clean = "DELETE FROM $wpdb->comments WHERE comment_approved = '0';";
				$comments = $wpdb->query( $clean );
				$message .= $comments.__(' unapproved comments deleted<br>', 'wp-optimize');
				break;
	
			case "optimize-db":
			   self::optimize_tables(true);
			   $message .= "Database ".DB_NAME." Optimized!<br>";
			   break;
		
			default:
				$message .= __('NO Actions Taken<br>', 'wp-optimize');
				break;
		} // end of switch
		
		
	return $message;

	} // end of function
	
	function optimize_tables($Optimize=false){
	
		$db_clean = DB_NAME;
			
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result))
			{
				$local_query = 'OPTIMIZE TABLE '.$row[0];
				$resultat  = mysql_query($local_query);
			}
		}
	
	}

}
?>