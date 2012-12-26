<?php 

/*
*
*  Pixaria Gallery
*	Copyright Jamie Longstaff
*
*/

// Set the include path for files used in this script
ini_set("include_path","../includes/");

// Load in the Pixaria settings and includes
include ("pixaria.Initialise.php");

// Set the site section
$admin_page_section = "user";

// Send HTML content HTTP header and don't cache
pix_http_headers("html","");

// Initialise the Smarty object
$smarty = new Smarty_Pixaria;

if ($objEnvData->fetchPost('cmd') == "12") {

	// Check that the user is an image_editor
	$objSecurity->authoriseUser("image_editor");
	
} else {

	// Check that the user is a administrator
	$objSecurity->authoriseUser("administrator");
	
}

$obj = new Admin_User();

class Admin_User {

	/*
	*
	*
	*
	*/
	function Admin_User () {
	
		global 	$objEnvData,
				$smarty,
				$ses,
				$cfg;

		// Load an initialise the database class
		require_once ('class.Database.php');
		require_once (SYS_BASE_PATH . 'resources/classes/class.InputData.php');

		$this->db		= new Database();
		$this->input	= new InputData();
		$this->view 	=& $smarty;
		$this->config	= $cfg;
		$this->session 	= $ses;
		
		// Assign an array of form actions to the view object
		$this->view->assign('form_actions', array (

			'images_delete' 		=> '{&quot;url&quot;:&quot;'.SYS_BASE_URL.FILE_ADM_IMAGE.'&quot;,&quot;cmd&quot;:&quot;formDeleteFromLibrary&quot;}',
			'images_batch_edit' 	=> '{&quot;url&quot;:&quot;'.SYS_BASE_URL.FILE_ADM_IMAGE.'&quot;,&quot;cmd&quot;:&quot;editMultiple&quot;}',
			'gallery_images_create' => '{&quot;url&quot;:&quot;'.SYS_BASE_URL.FILE_ADM_GALLERY.'&quot;,&quot;cmd&quot;:&quot;formCreateNewGallery&quot;}',
			'gallery_images_add' 	=> '{&quot;url&quot;:&quot;'.SYS_BASE_URL.FILE_ADM_GALLERY.'&quot;,&quot;cmd&quot;:&quot;formAddImagesToGallery&quot;}',
			'images_bulk_edit' 		=> '{&quot;url&quot;:&quot;'.SYS_BASE_URL.FILE_ADM_IMAGE.'&quot;,&quot;cmd&quot;:&quot;formEditBulk&quot;}'
		
		));
		
		switch ($this->input->name('cmd')) {
			
			case 'new':
				$this->createNewUserForm();
			break;
		
			case 'new_user':
				$this->createNew_UserForm();
			break;
			
			case 'createNewUser':
				$this->createNewUser();
			break;
		
			case 'createNewPartialUser':
				$this->createNewPartialUser();
			break;
			
			case 'edit':
				$this->formEdit();
			break;
			
			case 'save':
				$this->saveUser();
			break;
			
			case 'formConfirmDeleteUsers':
				$this->formConfirmDeleteUsers();
			break;
			
			case 'actionDeleteUsers':
				$this->actionDeleteUsers();
			break;
			
			case "promoteAdmin":
				$this->actionChangePrivileges('promoteAdmin');
			break;
			
			case "promoteActive":
				$this->actionChangePrivileges('promoteActive');
			break;
			
			case "promotePhotographer":
				$this->actionChangePrivileges('promotePhotographer');
			break;
			
			case "promoteDownload":
				$this->actionChangePrivileges('promoteDownload');
			break;
			
			case "demoteAdmin":
				$this->actionChangePrivileges('demoteAdmin');
			break;
			
			case "demoteActive":
				$this->actionChangePrivileges('demoteActive');
			break;
			
			case "demotePhotographer":
				$this->actionChangePrivileges('demotePhotographer');
			break;
			
			case "demoteDownload":
				$this->actionChangePrivileges('demoteDownload');
			break;
			
			case 'viewUserImages':
				$this->viewUserImages();
			break;
			
			default:
				$this->listAllUsers();
			break;
		}
	
	}
	
	/*
	*
	*	
	*
	*/
	function listAllUsers () {
		
		global $smarty, $objEnvData, $ses;
		
		$page = $this->input->name('page');
		
		$is_administrator	= $this->input->name('is_administrator');
		$is_photographer	= $this->input->name('is_photographer');
		$is_download		= $this->input->name('is_download');
		$is_disabled		= $this->input->name('is_disabled');
		$download			= $this->input->name('download');
		$photographer		= $this->input->name('photographer');
		$administrator		= $this->input->name('administrator');
		$inactive			= $this->input->name('inactive');
		$sort				= $this->input->name('sort');
		$method				= $this->input->name('method');
		$filter_email		= $this->input->name('filter_email');
		$filter_name		= $this->input->name('filter_name');
		
		// Tell Smarty if we're going to show the list after a completed action
		$this->view->assign("action_complete",$this->action_complete);
		
		switch ($sort) {
		
			case "name1":
			
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".family_name ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".family_name DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
			case "name2":
			
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".first_name ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".first_name DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
			case "name3":
			
				if ($method == "a" || !$method) {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".pseudonym ASC";
					$this->view->assign("new_method","d");
					
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".pseudonym DESC";
					$this->view->assign("new_method","a");
					
				}
				
			break;
			
			case "enabled":
			
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".account_status ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".account_status DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
			case "email":
			
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".email_address ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".email_address DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
			case "date": default:
				
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".date_registered ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".date_registered DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
			default:
				
				$sort 	= "date";
				$method = "d";
				$this->view->assign("new_method","a");
				
				if ($method == "a" || !$method) {
				
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".date_registered ASC";
					$this->view->assign("new_method","d");
				
				} else {
					
					$sql_order = "ORDER BY ".PIX_TABLE_USER.".date_registered DESC";
					$this->view->assign("new_method","a");
				
				}
			
			break;
			
		}
		
		// Set the active icon toolbar
		$this->view->assign('iconbar','listallusers');
		
		if ($inactive == 1 || $is_disabled) {
			
			$sql_where .= " AND ".PIX_TABLE_USER.".account_status = '0' ";
			
			$this->view->assign("type","inactive=".$inactive);
			$this->view->assign("is_disabled",true);
		
		}
		
		if ($administrator == 1 || $is_administrator) {
			
			$sql_filter_1 = " AND ";
			$sql_filter_2[] = " ".PIX_TABLE_SGME.".sys_group_id = '1' ";
			
			$this->view->assign("type","is_administrator=1");
			$this->view->assign("is_administrator",true);
			
			// Set the active icon toolbar
			$this->view->assign('iconbar','listadminusers');
		
		}
		
		if ($photographer == 1 || $is_photographer) {
			
			$sql_filter_1 = " AND ";
			$sql_filter_2[] = " ".PIX_TABLE_SGME.".sys_group_id = '3' ";
			
			$this->view->assign("type","is_photographer=1");
			$this->view->assign("is_photographer",true);
			
			// Set the active icon toolbar
			$this->view->assign('iconbar','listcontributors');
		
		}
		
		if ($download == 1 || $is_download) {
			
			$sql_filter_1 = " AND ";
			$sql_filter_2[] = " ".PIX_TABLE_SGME.".sys_group_id = '5' ";
			
			$this->view->assign("type","is_download=1");
			$this->view->assign("is_download",true);
		
		}
		
		if (is_array($sql_filter_2)) {
			
			$sql_having = $sql_filter_1 . "(" . implode(" OR ",$sql_filter_2) . ")";
		
		}
		
		if ($filter_email != "") {
			
			$sql_where .= " AND ".PIX_TABLE_USER.".email_address LIKE '%$filter_email%' ";
			
			$this->view->assign("filter_email",$filter_email);
		
		}
		
		if ($filter_name != "") {
			
			$sql_where .= " AND (".PIX_TABLE_USER.".first_name LIKE '%$filter_name%' OR ".PIX_TABLE_USER.".family_name LIKE '%$filter_name%' OR ".PIX_TABLE_USER.".pseudonym LIKE '%$filter_name%') ";
			
			$this->view->assign("filter_name",$filter_name);
		
		}
		
		$sql = "SELECT ".PIX_TABLE_USER.".*
											
				,CONCAT(".PIX_TABLE_USER.".family_name,', ',".PIX_TABLE_USER.".first_name) AS user_name
				
				FROM ".PIX_TABLE_USER." 
											
				LEFT JOIN ".PIX_TABLE_GRME." ON ".PIX_TABLE_USER.".userid = ".PIX_TABLE_GRME.".userid
				
				LEFT JOIN ".PIX_TABLE_SGME." ON ".PIX_TABLE_USER.".userid = ".PIX_TABLE_SGME.".sys_userid
				
				WHERE ".PIX_TABLE_USER.".userid AND ".PIX_TABLE_USER.".email_address != ''
				
				$sql_where
				
				$sql_having
				
				GROUP BY ".PIX_TABLE_USER.".userid
				
				$sql_order";

		// Count the total number of orphan images
		$total	= $this->db->sqlCountSelectRows($sql);
		
		if ($this->input->name('action') != "exportUsersAsCSV") {
			$page_sql = getMultiImagePageLimitSQL($page);
		}
		
		// Get an array of users from the database
		$user_data = $this->db->sqlSelectRows($sql . $page_sql);
		
		$query_string = "char=$char&amp;sort=$sort&amp;administrator=$administrator&amp;inactive=$inactive&amp;photographer=$photographer&amp;download=$download&amp;filter_name=$filter_name&amp;filter_email=$filter_email";
		
		$ipages = getMultiImagePageNavigation ("users", $page, $total, $query_string);
		
		if (is_array($user_data) && count($user_data) > 0) {
		
			foreach($user_data as $key => $value) {
				
				$userid[]				= $value['userid'];
				$email_address[]		= $value['email_address'];
				$user_name[]			= $value['user_name'];
				$first_name[]			= $value['first_name'];
				$family_name[]			= $value['family_name'];
				$pseudonym[]			= $value['pseudonym'];
				$date_registered[]		= strtotime($value['date_registered']);
				$date_edited[]			= strtotime($value['date_edited']);
				$date_expiration[]		= strtotime($value['date_expiration']);
				$account_status[]		= $value['account_status'];
				$addr1[]				= $value['addr1'];
				$addr2[]				= $value['addr2'];
				$addr3[]				= $value['addr3'];
				$country[]				= $value['country'];
				$postal_code[]			= $value['postal_code'];
				$telephone[]			= $value['telephone'];
				$fax[]					= $value['fax'];
				$city[]					= $value['city'];
				$region[]				= $value['region'];
				$ldap_auth_flag[]		= $value['ldap_auth_flag'];
				$password[]				= $value['password'] == "" ? 0:1;
				
			}
		
			// Assign the number of users to Smarty
			$this->view->assign("user_count",count($user_data));
		
		} else {
		
			// Assign the number of users to Smarty
			$this->view->assign("user_count",0);
		
		}
		
		// Assign users to Smarty
		$this->view->assign("userid",$userid);
		$this->view->assign("email_address",$email_address);
		$this->view->assign("user_name",$user_name);
		$this->view->assign("family_name",$family_name);
		$this->view->assign("first_name",$first_name);
		$this->view->assign("pseudonym",$pseudonym);
		$this->view->assign("date_registered",$date_registered);
		$this->view->assign("date_edited",$date_edited);
		$this->view->assign("date_expiration",$date_expiration);
		$this->view->assign("account_status",$account_status);
		$this->view->assign("addr1",$addr1);
		$this->view->assign("addr2",$addr2);
		$this->view->assign("addr3",$addr3);
		$this->view->assign("country",$country);
		$this->view->assign("postal_code",$postal_code);
		$this->view->assign("telephone",$telephone);
		$this->view->assign("fax",$fax);
		$this->view->assign("city",$city);
		$this->view->assign("region",$region);
		$this->view->assign("ldap_auth_flag",$ldap_auth_flag);
		$this->view->assign("password",$password);
		$this->view->assign("query_string",$query_string);
		$this->view->assign("ipage_current",$ipages[0]);
		$this->view->assign("ipage_numbers",$ipages[1]);
		$this->view->assign("ipage_links",$ipages[2]);
		$this->view->assign("sort",$sort);
		$this->view->assign("method",$method);
			
		$this->view->assign("administrator",$administrator);
		$this->view->assign("download",$download);
		$this->view->assign("inactive",$inactive);
		$this->view->assign("photographer",$photographer);
					
		if ($this->input->name('action') == "exportUsersAsCSV") {
			
			// Send CSV force download headers
			pix_http_headers("csv","","userdata.csv");
			
			// Output the users page
			$this->view->display('user/user.export.csv.txt');
		
		} else {
			
			// Send HTML content HTTP header and don't cache
			pix_http_headers("html","");
			
			// Set the page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_01']);
			
			// Output the users page
			$this->view->display('user/users.list.html');
		
		}
		
	}
	
	function formEdit () {
		
		global $cfg, $ses, $lang, $objEnvData;
		
		// Get the userid
		$userid = $this->input->name('userid');
		
		// Get the list of system groups this user is a member of
		$result = $this->db->rows("SELECT * FROM " . PIX_TABLE_SGME . " WHERE sys_userid = '".$this->db->escape($userid)."' ORDER by sys_group_id ASC");
		
		$is_admin	= (bool)FALSE;
		$is_editor	= (bool)FALSE;
		$is_photo	= (bool)FALSE;
		
		if (is_array($result)) {
			
			foreach ($result as $key => $value) {
				
				// Set whether or not this user is a an admin or image editor
				if ($value['sys_group_id']==1) { $is_admin					= (bool)TRUE; }
				if ($value['sys_group_id']==2) { $is_edit_own				= (bool)TRUE; }
				if ($value['sys_group_id']==3) { $is_photo					= (bool)TRUE; }
				if ($value['sys_group_id']==4) { $is_delete					= (bool)TRUE; }
				if ($value['sys_group_id']==5) { $is_download				= (bool)TRUE; }
				if ($value['sys_group_id']==6) { $is_edit_all				= (bool)TRUE; }
				if ($value['sys_group_id']==7) { $is_contrib_auto_approve	= (bool)TRUE; }
			
			}
			
			$this->view->assign("is_admin",$is_admin);
			$this->view->assign("is_edit_own",$is_edit_own);
			$this->view->assign("is_photo",$is_photo);
			$this->view->assign("is_delete",$is_delete);
			$this->view->assign("is_download",$is_download);
			$this->view->assign("is_edit_all",$is_edit_all);
			$this->view->assign("is_contrib_auto_approve",$is_contrib_auto_approve);
			
		}
		
		
		// Get the list of groups this user is a member of
		$result = $this->db->rows("SELECT * FROM " . PIX_TABLE_GRME . " WHERE userid = '".$this->db->escape($userid)."' ORDER by group_id ASC");
		
		if (is_array($result)) {
		
			// Clean the list of groups and sort into an array
			if (count($result) > 0 && is_array($result)) {
				foreach($result as $key => $value) {
					if ($result[$key]['group_id'] > 2) { $member_of[] = $result[$key]['group_id']; }
				}
			}
				
			
		}
		
		// Get the lists of group names and ids
		$group_list = groupListArray();
		
		if (is_array($member_of)) {
		
			// Check which groups are selected for this gallery
			foreach ($group_list[1] as $key => $value) {
			
				if (in_array($value,$member_of)) { $group_member_id[] = "$value"; } else { $group_member_id[] = ""; }
			
			}
		
		}
		
		// Get information about required registration details
		$result = $this->db->rows("SELECT * FROM ".PIX_TABLE_USDA);
		
		if (is_array($result)) {
		
			//	Feed data into Smarty
			for ($i=0; $i<count($result); $i++) {
			
				$name = $result[$i]['name'];
				
				$this->view->assign("data_".$name,$result[$i]['value']);
			
			}
		
		}
		
		// Load the group list data into Smarty
		$this->view->assign("group_name",$group_list[0]);
		$this->view->assign("group_id",$group_list[1]);
		$this->view->assign("group_member_id",$group_member_id);
		
		require_once('class.PixariaUser.php');
		
		$objPixariaUser = new PixariaUser($userid);
		
		$email_address 				= $objPixariaUser->getEmail();
		$formal_title 				= $objPixariaUser->getSalutation();
		$other_title 				= $objPixariaUser->getOtherSalutation();
		$first_name 				= $objPixariaUser->getFirstName();
		$middle_initial 			= $objPixariaUser->getInitial();
		$family_name				= $objPixariaUser->getFamilyName();
		$pseudonym					= $objPixariaUser->getPseudonym();
		$password_rem_que			= $objPixariaUser->getReminderQuestion();
		$password_rem_ans			= $objPixariaUser->getReminderAnswer();
		$date_registered 			= $objPixariaUser->getDateRegistered();
		$date_edited 				= $objPixariaUser->getDateEdited();
		$date_expiration 			= $objPixariaUser->getDateExpires();
		$account_status 			= $objPixariaUser->getAccountStatus();
		$telephone 					= $objPixariaUser->getTelephone();
		$mobile 					= $objPixariaUser->getMobile();
		$fax 						= $objPixariaUser->getFax();
		$addr1 						= $objPixariaUser->getAddress1();
		$addr2 						= $objPixariaUser->getAddress2();
		$addr3 						= $objPixariaUser->getAddress3();
		$city 						= $objPixariaUser->getCity();
		$region 					= $objPixariaUser->getRegion();
		$country 					= $objPixariaUser->getCountry();
		$postal_code 				= $objPixariaUser->getPostCode();
		$other_business_type 		= $objPixariaUser->getBusinessType();
		$other_business_position 	= $objPixariaUser->getBusinessPosition();
		$other_image_interest 		= $objPixariaUser->getInterest();
		$other_frequency 			= $objPixariaUser->getPubFrequency();
		$other_circulation 			= $objPixariaUser->getPubCirculation();
		$other_territories 			= $objPixariaUser->getTerritories();
		$other_website 				= $objPixariaUser->getWebsite();
		$other_company_name 		= $objPixariaUser->getCompanyName();
		$other_message 				= $objPixariaUser->getMessage();
		$expiry_mode 				= $objPixariaUser->getExpiryMode();
		$expiry_days 				= $objPixariaUser->getExpiryDays();
		$gallery_rights				= $objPixariaUser->getGalleryRights();
		$ldap_auth_flag				= $objPixariaUser->getLdapAuthFlag();
		$ldap_username				= $objPixariaUser->getLdapUsername();
		
		// Assign user variables to smarty
		$this->view->assign("userid",$userid);
		$this->view->assign("formal_title",stripslashes($formal_title));
		$this->view->assign("other_title",stripslashes($other_title));
		$this->view->assign("first_name",stripslashes($first_name));
		$this->view->assign("middle_initial",stripslashes($middle_initial));
		$this->view->assign("family_name",stripslashes($family_name));
		$this->view->assign("pseudonym",stripslashes($pseudonym));
		$this->view->assign("email_address",stripslashes($email_address));
		$this->view->assign("telephone",stripslashes($telephone));
		$this->view->assign("mobile",stripslashes($mobile));
		$this->view->assign("fax",stripslashes($fax));
		$this->view->assign("addr1",stripslashes($addr1));
		$this->view->assign("addr2",stripslashes($addr2));
		$this->view->assign("addr3",stripslashes($addr3));
		$this->view->assign("city",stripslashes($city));
		$this->view->assign("region",stripslashes($region));
		$this->view->assign("country",stripslashes($country));
		$this->view->assign("postal_code",stripslashes($postal_code));
		$this->view->assign("password_rem_que",stripslashes($password_rem_que));
		$this->view->assign("password_rem_ans",stripslashes($password_rem_ans));
		$this->view->assign("date_registered",strtotime($date_registered));
		$this->view->assign("date_edited",strtotime($date_edited));
		$this->view->assign("date_expiration",$date_expiration);
		$this->view->assign("account_status",$account_status);
		$this->view->assign("other_business_type",stripslashes($other_business_type));
		$this->view->assign("other_business_position",stripslashes($other_business_position));
		$this->view->assign("other_image_interest",stripslashes($other_image_interest));
		$this->view->assign("other_frequency",stripslashes($other_frequency));
		$this->view->assign("other_circulation",stripslashes($other_circulation));
		$this->view->assign("other_territories",stripslashes($other_territories));
		$this->view->assign("other_website",stripslashes($other_website));
		$this->view->assign("other_company_name",stripslashes($other_company_name));
		$this->view->assign("other_message",stripslashes($other_message));
		$this->view->assign("expiry_mode",$expiry_mode);
		$this->view->assign("expiry_days",$expiry_days);
		$this->view->assign("ldap_auth_flag",$ldap_auth_flag);
		$this->view->assign("ldap_username",$ldap_username);

		$this->view->assign("sales_tax",$objPixariaUser->getSalesTax());
		$this->view->assign("reg_custom_01",$objPixariaUser->getRegCustom01());
		$this->view->assign("reg_custom_02",$objPixariaUser->getRegCustom02());
		$this->view->assign("reg_custom_03",$objPixariaUser->getRegCustom03());
		$this->view->assign("reg_custom_04",$objPixariaUser->getRegCustom04());
		$this->view->assign("reg_custom_05",$objPixariaUser->getRegCustom05());
		$this->view->assign("marketing_contact",$objPixariaUser->getMarketingContact());

		$this->view->assign("auto_import",$gallery_rights['auto_import']);
		
		// Load Gallery Core class
		require_once (SYS_BASE_PATH . 'resources/classes/class.Core.Gallery.php');
		
		// Instantiate the Gallery Core object
		$this->GalleryCore = new GalleryCore();
		
		// Load an array of nested galleries
		$gallery_list = $this->GalleryCore->galleryListingArray();
		
		// Load the category list data into Smarty
		$this->view->assign("menu_gallery_title",	$gallery_list[0]);
		$this->view->assign("menu_gallery_id",		$gallery_list[1]);
		
		$sql = "SELECT gallery_id FROM ".PIX_TABLE_GDLU." WHERE user_id = ".$this->db->escape($userid)."";
		
		list ($gallery_download) = $this->db->rowsAsColumns($sql);
		
		$this->view->assign('gallery_download',$gallery_download);
		
		// Load the country class
		require_once ("class.PixariaCountry.php");
		
		// Initialise the country class
		$objCountry = new PixariaCountry();
		
		// Assign country data to Smarty
		$this->view->assign("iso_iso",$objCountry->getIsoIso());
		$this->view->assign("name_iso",$objCountry->getNameIso());
		
		$sql = "SELECT 		 gall.gallery_id
							,gall.gallery_title
							,COUNT(glog.gallery_log_id)
				
				FROM ".PIX_TABLE_GLOG." glog
				
				LEFT JOIN ".PIX_TABLE_GALL." gall ON gall.gallery_id = glog.gallery_id
				
				WHERE gall.gallery_id IS NOT NULL
				
				AND glog.user_id = '".$userid."'
				
				GROUP BY gall.gallery_id";
		
		list (
			
			$gallery_id,
			$gallery_title,
			$view_count
		
		) = $this->db->rowsAsColumns($sql);
		
		$this->view->assign('gallery_id',$gallery_id);
		$this->view->assign('gallery_title',$gallery_title);
		$this->view->assign('view_count',$view_count);
		
		$result = $this->db->rows("SELECT * FROM ". PIX_TABLE_USDA, MYSQL_ASSOC);
		
		if (is_array($result)) {
		
			//	Feed data into Smarty
			for ($i=0; $i<count($result); $i++) {
			
				$name = $result[$i]['name'];
				
				$this->view->assign($name.'_name',$result[$i]['description']);
			
			}
		
		}

		// Define html page title
		$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_02']);
				
		// Output html from template file
		$this->view->display('user/form.edit.html');
		
	}
	
	/*
	*
	*	
	*
	*/
	function saveUser () {
		
		global $cfg, $ses, $lang, $objEnvData;
		
		require_once ("class.PixariaUser.php");	
		
		$user = new PixariaUser($this->input->post('userid'));
		
		$user_original_status = $user->getAccountStatus();
		
		$user->setSalutation($this->input->post('formal_title'));
		$user->setFirstName($this->input->post('first_name'));
		$user->setFamilyName($this->input->post('family_name'));
		$user->setPseudonym($this->input->post('pseudonym'));
		$user->setEmail($this->input->post('email_address'));
		$user->setIsAdministrator($this->input->post('is_admin'));
		$user->setIsEditOwn($this->input->checkbox('is_edit_own'));
		$user->setIsEditAll($this->input->checkbox('is_edit_all'));
		$user->setIsPhotographer($this->input->post('is_photo'));
		$user->setIsDelete($this->input->post('is_delete'));
		$user->setIsContributorAutoApprove($this->input->checkbox('is_contrib_auto_approve'));
		$user->setIsDownload($this->input->post('is_download'));
		$user->setGroups($this->input->post('groups'));
		$user->setPassword1($this->input->post('password_1'));
		$user->setPassword2($this->input->post('password_2'));
		$user->setReminderQuestion($this->input->post('password_rem_que'));
		$user->setReminderAnswer($this->input->post('password_rem_ans'));
		$user->setAccountStatus($this->input->post('account_status'));
		$user->setTelephone($this->input->post('telephone'));
		$user->setMobile($this->input->post('mobile'));
		$user->setFax($this->input->post('fax'));
		$user->setAddress1($this->input->post('addr1'));
		$user->setAddress2($this->input->post('addr2'));
		$user->setAddress3($this->input->post('addr3'));
		$user->setCity($this->input->post('city'));
		$user->setRegion($this->input->post('region'));
		$user->setCountry($this->input->post('country'));
		$user->setPostCode($this->input->post('postal_code'));
		$user->setBusinessType($this->input->post('other_business_type'));
		$user->setPosition($this->input->post('other_business_position'));
		$user->setInterest($this->input->post('other_image_interest'));
		$user->setPubFrequency($this->input->post('other_frequency'));
		$user->setPubCirculation($this->input->post('other_circulation'));
		$user->setTerritories($this->input->post('other_territories'));
		$user->setWebsite($this->input->post('other_website'));
		$user->setCompanyName($this->input->post('other_company_name'));
		$user->setMessage($this->input->post('other_message'));
		$user->setExpiryMode($this->input->post('expiry_mode'));
		$user->setExpiryDays($this->input->post('expiry_days'));
		$user->setDateExpires($this->input->post('expiry_date_Day'), $this->input->post('expiry_date_Month'), $this->input->post('expiry_date_Year'));
		$user->setLdapAuthFlag($this->input->checkbox('ldap_auth_flag'));
		$user->setLdapUsername($this->input->post('ldap_username'));
		$user->setSalesTax($this->input->post('sales_tax'));
		$user->setRegCustom01($this->input->post('reg_custom_01'));
		$user->setRegCustom02($this->input->post('reg_custom_02'));
		$user->setRegCustom03($this->input->post('reg_custom_03'));
		$user->setRegCustom04($this->input->post('reg_custom_04'));
		$user->setRegCustom05($this->input->post('reg_custom_05'));
		$user->setGalleryDownload($this->input->post('gallery_download'));
		$user->setGalleryAutoImport($this->input->post('auto_import'));
		
		// Edit the user's profile information
		// Apply data validation in mode = 'user' and type = 'edit'
		$success = $user->editUser('admin','edit',false);
		
		if ($success) { // The new user account is now complete
			
			// If the user is being activated
			if (!$user_original_status && $this->input->checkbox('account_status')) {
			
				// Load the Email class
				require_once ('class.PixariaEmail.php');
				
				// Initialise the Email object
				$objEmail = new PixariaEmail();
				
				$this->view->assign('to_name',$this->input->post('first_name').' '.$this->input->post('family_name'));
				
				// Set the object properties
				$objEmail->setSubject($cfg['set']['site_name']." Profile Updated");
				$objEmail->setMessage($this->view->fetch('email.templates/account.edit.html'));
				$objEmail->setRecipientAddress($this->input->post('email_address'));
				$objEmail->setSenderAddress($cfg['set']['contact_email']);
				$objEmail->setSenderName($cfg['set']['contact_name']);
				
				// Send the Email
				$objEmail->sendEmail();
			
			}
			
			header("Content-type: application/json");
			
			print '{"message":"'.$GLOBALS['_STR_']['CODE_ADMIN_USER_03'].'"}';
						
		} else { // The validation failed and the user needs to edit some bits
				
			$this->view->assign("profile_errors",$user->getProfileErrors());
			$this->view->assign("problem",true);
			
			// Tell smarty object that there were errors
			$this->view->assign("problem",(bool)1);
			
			// Assign problem message to smarty object
			$this->view->assign("problem_output",$problem_output);
				
			// Output html from template file
			$this->view->display('user/form.edit.error.html');
		
		}
	
	}
	
	/*
	*
	*	Shows a form allowing administrator to create a new user invitation
	*
	*/
	function createNewUserForm () {
	
		// Get the lists of group names and ids
		$group_list = generateUserGroupsArray();

		// Load the group list data into Smarty
		$this->view->assign("group_name",$group_list[0]);
		$this->view->assign("group_id",$group_list[1]);
		
		// Define html page title
		$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_04']);
		
		// Set the active icon toolbar
		$this->view->assign('iconbar','inviteuser');
		
		// Output html from template file
		$this->view->display('user/new.user.html');
	
		// Stop running the script here
		exit;
		
	}
	
	function createNew_UserForm() {
	
		// Get the lists of group names and ids
		$group_list = generateUserGroupsArray();

		// Load the group list data into Smarty
		$this->view->assign("group_name",$group_list[0]);
		$this->view->assign("group_id",$group_list[1]);
		
		// Define html page title
		$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_04']);
		
		// Set the active icon toolbar
		$this->view->assign('iconbar','inviteuser');
		
		// Output html from template file
		$this->view->display('user/new_user.user.html');
	
		// Stop running the script here
		exit;
		
	}
	
	function createNewUser() {
		
		global $objEnvData, $cfg;
		
		require_once ("class.PixariaUser.php");	
		
		$objPixariaUser = new PixariaUser();
		
		$objPixariaUser->setFirstName($this->input->post('first_name'));
		$objPixariaUser->setFamilyName($this->input->post('family_name'));
		$objPixariaUser->setgender($this->input->post('gender'));
		$objPixariaUser->setcalender($this->input->post('calender'));
		$objPixariaUser->setPseudonym($this->input->post('pseudonym'));
		$objPixariaUser->setEmail($this->input->post('email_address'));
		$objPixariaUser->setIsAdministrator($this->input->checkbox('is_admin'));
		$objPixariaUser->setIsEditOwn($this->input->checkbox('is_editor'));
		$objPixariaUser->setIsPhotographer($this->input->checkbox('is_photo'));
		$objPixariaUser->setGroups($this->input->post('groups'));
		$objPixariaUser->setExpiryMode($this->input->post('expiry_mode'));
		$objPixariaUser->setExpiryDays($this->input->post('expiry_days'));
		$objPixariaUser->setDateExpires($this->input->post('expiry_date_Day'), $this->input->post('expiry_date_Month'), $this->input->post('expiry_date_Year'));
		$objPixariaUser->setLdapAuthFlag($this->input->checkbox('ldap_auth_flag'));
		$objPixariaUser->setLdapUsername($this->input->post('ldap_username'));
		
		$success = $objPixariaUser->createNewUserMinimal();
		
		// Check whether the create user function worked or not
		
		if ($success) { // Yes, it worked and we can send the invite
			die("here");
			// Define html page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_05']);
			
			// Output html from template file
			$this->view->display('user/new.user.success.html');
		
			// Stop running the script here
			exit;
			
		} else { // The user was not created because there was a problem with the data submitted
			
			$this->view->assign('first_name',$this->input->post('first_name'));
			$this->view->assign('family_name',$this->input->post('family_name'));
			$this->view->assign('pseudonym',$this->input->post('pseudonym'));
			$this->view->assign('email_address',$this->input->post('email_address'));
			$this->view->assign('is_admin',$this->input->checkbox('is_admin'));
			$this->view->assign('is_editor',$this->input->checkbox('is_editor'));
			$this->view->assign('is_photo',$this->input->checkbox('is_photo'));
			$this->view->assign('group_member_id',$this->input->post('groups'));
			$this->view->assign('expiry_mode',$this->input->post('expiry_mode'));
			$this->view->assign('expiry_days',$this->input->post('expiry_days'));
			$this->view->assign('ldap_auth_user',$this->input->post('ldap_auth_user'));
			$this->view->assign('ldap_username',$this->input->post('ldap_username'));
			$this->view->assign('date_expiration',$this->input->post('expiry_date_Year').'-'.$this->input->post('expiry_date_Month').'-'.$this->input->post('expiry_date_Day'));
			
			// Load the errror information from the PixariaUsers object
			$this->view->assign("profile_errors",$objPixariaUser->getProfileErrors());
			
			// Get the lists of group names and ids
			$group_list = generateUserGroupsArray();
	
			// Load the group list data into Smarty
			$this->view->assign("group_name",$group_list[0]);
			$this->view->assign("group_id",$group_list[1]);
			
			// Define html page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_06']);
			
			// Output html from template file
			$this->view->display('user/new_user.user.html');
		
			// Stop running the script here
			exit;
			
		}
		
	}
	
	/*
	*
	*	Process the user invitation
	*
	*/
	function createNewPartialUser () {
		
		global $objEnvData, $cfg;
		
		require_once ("class.PixariaUser.php");	
		
		$objPixariaUser = new PixariaUser();
		
		$objPixariaUser->setFirstName($this->input->post('first_name'));
		$objPixariaUser->setFamilyName($this->input->post('family_name'));
		$objPixariaUser->setPseudonym($this->input->post('pseudonym'));
		$objPixariaUser->setEmail($this->input->post('email_address'));
		$objPixariaUser->setIsAdministrator($this->input->checkbox('is_admin'));
		$objPixariaUser->setIsEditOwn($this->input->checkbox('is_editor'));
		$objPixariaUser->setIsPhotographer($this->input->checkbox('is_photo'));
		$objPixariaUser->setGroups($this->input->post('groups'));
		$objPixariaUser->setExpiryMode($this->input->post('expiry_mode'));
		$objPixariaUser->setExpiryDays($this->input->post('expiry_days'));
		$objPixariaUser->setDateExpires($this->input->post('expiry_date_Day'), $this->input->post('expiry_date_Month'), $this->input->post('expiry_date_Year'));
		$objPixariaUser->setLdapAuthFlag($this->input->checkbox('ldap_auth_flag'));
		$objPixariaUser->setLdapUsername($this->input->post('ldap_username'));
		
		$success = $objPixariaUser->createNewUserMinimal();
		
		// Check whether the create user function worked or not
		
		if ($success) { // Yes, it worked and we can send the invite
			
			// Define html page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_05']);
			
			// Output html from template file
			$this->view->display('user/new.user.success.html');
		
			// Stop running the script here
			exit;
			
		} else { // The user was not created because there was a problem with the data submitted
			
			$this->view->assign('first_name',$this->input->post('first_name'));
			$this->view->assign('family_name',$this->input->post('family_name'));
			$this->view->assign('pseudonym',$this->input->post('pseudonym'));
			$this->view->assign('email_address',$this->input->post('email_address'));
			$this->view->assign('is_admin',$this->input->checkbox('is_admin'));
			$this->view->assign('is_editor',$this->input->checkbox('is_editor'));
			$this->view->assign('is_photo',$this->input->checkbox('is_photo'));
			$this->view->assign('group_member_id',$this->input->post('groups'));
			$this->view->assign('expiry_mode',$this->input->post('expiry_mode'));
			$this->view->assign('expiry_days',$this->input->post('expiry_days'));
			$this->view->assign('ldap_auth_user',$this->input->post('ldap_auth_user'));
			$this->view->assign('ldap_username',$this->input->post('ldap_username'));
			$this->view->assign('date_expiration',$this->input->post('expiry_date_Year').'-'.$this->input->post('expiry_date_Month').'-'.$this->input->post('expiry_date_Day'));
			
			// Load the errror information from the PixariaUsers object
			$this->view->assign("profile_errors",$objPixariaUser->getProfileErrors());
			
			// Get the lists of group names and ids
			$group_list = generateUserGroupsArray();
	
			// Load the group list data into Smarty
			$this->view->assign("group_name",$group_list[0]);
			$this->view->assign("group_id",$group_list[1]);
			
			// Define html page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_06']);
			
			// Output html from template file
			$this->view->display('user/new.user.html');
		
			// Stop running the script here
			exit;
			
		}
		
	}
	
	/*
	*
	*	This method deletes one or more users with ids in array $users
	*
	*/
	function actionDeleteUsers () {
	
		global $objEnvData, $cfg, $ses;
		
		$users = $this->input->name('users');
		
		if (is_array($users)) {
		
			foreach ($users as $key => $value) {
				
				if ($value != $ses['psg_userid'] && is_numeric($value)) {
					
					// Delete the user from the users table
					$this->db->query("DELETE FROM ".PIX_TABLE_USER." WHERE userid = '".$this->db->escape($value)."'");
					
					// Delete the user from the groups table
					$this->db->query("DELETE FROM ".PIX_TABLE_GRPS." WHERE userid = '".$this->db->escape($value)."'");
								
					// Delete the user from the lightbox table
					$this->db->query("DELETE FROM ".PIX_TABLE_LBOX." WHERE userid = '".$this->db->escape($value)."'");
								
					// Delete the user from the lightbox_members table
					$this->db->query("DELETE FROM ".PIX_TABLE_LBME." WHERE userid = '".$this->db->escape($value)."'");
								
					// Delete the user from the cart table
					$this->db->query("DELETE FROM ".PIX_TABLE_CART." WHERE userid = '".$this->db->escape($value)."'");
								
					// Delete the user from the cart_members table
					$this->db->query("DELETE FROM ".PIX_TABLE_CRME." WHERE userid = '".$this->db->escape($value)."'");
								
					// Delete the user from the transaction_messages table
					$this->db->query("DELETE FROM ".PIX_TABLE_TMSG." WHERE userid = '".$this->db->escape($value)."'");						
				
					// Delete the user from the user preferences table
					$this->db->query("DELETE FROM ".PIX_TABLE_UPRF." WHERE user_id = '".$this->db->escape($value)."'");						
		
					// Delete the user from the group members table
					$this->db->query("DELETE FROM ".PIX_TABLE_GRME." WHERE userid = '".$this->db->escape($value)."'");						
				
					// Delete the user from the group members table
					$this->db->query("DELETE FROM ".PIX_TABLE_SGME." WHERE sys_userid = '".$this->db->escape($value)."'");						
				
				}
				
			}
		
		}
	
		// Display the user list 
		$this->listAllUsers();			
				
	}
	
	/*
	*
	*	Method shows a confirmation page for deleting one or more users from the system
	*
	*/
	function formConfirmDeleteUsers () {
		
		global $cfg, $ses;
		
		$users = $this->input->name('users');
		
		if (is_array($users)) {
		
			$this->view->assign("users",$users);
			
			foreach ($users as $key => $value) {
			
				$userinfo			= getUserContactDetails($value);
				$user_name[]		= $userinfo[0];
				$email_address[]	= $userinfo[1];
				
			}
			
			$this->view->assign("user_count","1");
			$this->view->assign("email_address",$email_address);
			$this->view->assign("user_name",$user_name);
			
			// Send HTML content HTTP header and don't cache
			pix_http_headers("html","");
		
			// Set the page title
			$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_07']);
		
			// Output a page to confirm deleting of these user(s)
			$this->view->pixDisplay('user/form.delete.confirm.html');
				
		} else {
		
			// Display the user list 
			$this->listAllUsers();			
		
		}
	
	}

	/*
	*
	*	This method lists the images owned by a user with id $userid
	*
	*/
	function viewUserImages () {
	
		global $ses, $cfg;
		
		$page = $this->input->name('page');
		
		// Get the userid for the person whose images we want to look at
		$userid = $this->input->name('userid');
		
		// Load the user class
		require_once('class.PixariaUser.php');
		
		// Create the user object
		$objUser = new PixariaUser($userid);
		
		// Generate SQL to view the user's images
		$sql = "SELECT * FROM ".PIX_TABLE_IMGS."
				
				WHERE image_userid = '$userid'
				
				ORDER BY image_filename";
		
		// Count the total number of orphan images
		$total	= $this->db->sqlCountSelectRows($sql);
		
		// Select all images that are no in the gallery_order table (i.e. not yet in any galleries)
		$result = $this->db->sqlSelectRows($sql . getMultiImagePageLimitSQL($page));
		
		$query_string = "cmd=viewUserImages&amp;userid=$userid";
		
		$ipages = getMultiImagePageNavigation ("user_images", $page, $total, $query_string);
		
		if (is_array($result)) {
		
			// Initialise arrays to hold image data
			$image_id	 	= array();
			$image_active	= array();
			$image_filename = array();
			$image_path		= array();
			$image_date		= array();
			$icon_path		= array();
			$icon_width		= array();
			$icon_height	= array();
			$comp_path		= array();
			$comp_width		= array();
			$comp_height	= array();
			$image_width	= array();
			$image_height	= array();
			$image_title	= array();
		
			require_once('class.PixariaImage.php');
			
			foreach ($result as $key => $value) {
				
				$image_id[]			= $value['image_id'];
				$image_userid[]		= $value['image_userid'];
				$image_owner[]		= $value['image_owner'];
				$image_active[]		= $value['image_active'];
				$image_path[]		= $value['image_path'];
				$image_date[]		= $value['image_date'];
				
				$objImage = new PixariaImage($value['image_id']);
				
				$image_filename[]	= $objImage->getImageFileName();
				$image_filetype[]	= $objImage->getImageFileType();
				$image_title[] 		= $objImage->getImageTitle();
				$comp_url[] 		= $objImage->getImageCompAdminUrl();
				$comp_width[] 		= $objImage->getImageCompingWidth();
				$comp_height[] 		= $objImage->getImageCompingHeight();
								
				$original_width[] 	= $objImage->getImageOriginalWidth();
				$original_height[] 	= $objImage->getImageOriginalHeight();
				
				$image_width[]		= $objImage->getImageWidth();
				$image_height[]		= $objImage->getImageHeight();				

				$is_image[] 	= $objImage->getIsImage();
				$icon_html[] 	= $objImage->getImageIconHtml();
				$small_html[] 	= $objImage->getImageSmallHtml();
								
			}
			
			// Assign image information to Smarty
			$this->view->assign("is_image",$is_image);
			$this->view->assign("image_id",$image_id);
			$this->view->assign("image_owner",$image_owner);
			$this->view->assign("image_userid",$image_userid);
			$this->view->assign("image_active",$image_active);
			$this->view->assign("image_filename",$image_filename);
			$this->view->assign("image_path",$image_path);
			$this->view->assign("image_date",$image_date);
			$this->view->assign("icon_html",$icon_html);
			$this->view->assign("small_html",$small_html);
			$this->view->assign("comp_path",$comp_path);
			$this->view->assign("comp_url",$comp_url);
			$this->view->assign("comp_width",$comp_width);
			$this->view->assign("comp_height",$comp_height);
			$this->view->assign("image_width",$image_width);
			$this->view->assign("image_height",$image_height);
			$this->view->assign("image_title",$image_title);
			
			$this->view->assign("images_present",(bool)true);

			$this->view->assign("ipage_current",$ipages[0]);
			$this->view->assign("ipage_numbers",$ipages[1]);
			$this->view->assign("ipage_links",$ipages[2]);
			
		}	
		
		$this->view->assign("user_name",$objUser->getName());
		
		// Send HTML content HTTP header and don't cache
		pix_http_headers("html","");
		
		// Set the page title
		$this->view->assign("page_title",$GLOBALS['_STR_']['CODE_ADMIN_USER_08'].$objUser->getName());
		
		// Output the library page
		$this->view->display('user/files.list.html');
		
	}
		
	/*
	*
	*
	*
	*/
	function actionChangePrivileges ($cmd) {
	
		global $cfg;
		
		$users = $this->input->name('users');
		
		if (is_array($users)) {
		
			foreach ($users as $key => $value) {
			
				switch ($cmd) {
				
					case "promoteAdmin":
						$sql = "INSERT INTO ".PIX_TABLE_SGME." VALUES ('1','".$this->db->escape($value)."');";
						$_POST['administrator'] = "1";
					break;
				
					case "promoteActive":
						$sql = "UPDATE ".PIX_TABLE_USER." SET account_status = '1' WHERE userid = '".$this->db->escape($value)."';";
						$_POST['inactive'] = "1";
					break;
				
					case "promotePhotographer":
						$sql = "INSERT INTO ".PIX_TABLE_SGME." VALUES ('3','".$this->db->escape($value)."');";
						$_POST['photographer'] = "1";
					break;
				
					case "promoteDownload":
						$sql = "INSERT INTO ".PIX_TABLE_SGME." VALUES ('5','".$this->db->escape($value)."');";
						$_POST['download'] = "1";
					break;
				
					case "demoteAdmin":
						$sql = "DELETE FROM ".PIX_TABLE_SGME." WHERE sys_group_id = '1' AND sys_userid = '".$this->db->escape($value)."';";
						$_POST['administrator'] = "1";
					break;
				
					case "demoteActive":
						$sql = "UPDATE ".PIX_TABLE_USER." SET account_status = '0' WHERE userid = '".$this->db->escape($value)."';";
						$_POST['inactive'] = "1";
					break;
				
					case "demotePhotographer":
						$sql = "DELETE FROM ".PIX_TABLE_SGME." WHERE sys_group_id = '3' AND sys_userid = '$value';";
						$_POST['photographer'] = "1";
					break;
				
					case "demoteDownload":
						$sql = "DELETE FROM ".PIX_TABLE_SGME." WHERE sys_group_id = '5' AND sys_userid = '$value';";
						$_POST['download'] = "1";
					break;
				
				}
			
				$this->db->query($sql);
				
			}
			
			$this->action_complete = true;
			
			$this->listAllUsers();
			
			exit;
			
		} else {
		
			$this->listAllUsers();
			
			exit;
			
		}
		
	}

}

?>
