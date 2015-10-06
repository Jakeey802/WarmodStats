<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

//path to Directory to scan
$directory = "/home/logs/";
//get all log files in directory
$logs = glob($directory . "*.log");

if (count($logs) > 0){
	print "Updating stats site";
}else{
	exit("No log files to update");
} 

foreach($logs as $file){
	//ini_set('auto_detect_line_endings',true);
//	$file = "logfiles/log/2014-01-28-2055-69a5-de_cache.log";
	unset($playersArray);
	unset($FHStats);
	unset($SHStats);
	unset($FHOTStats);
	unset($SHOTStats);
	unset($FHWeaponStats);
	unset($SHWeaponStats);
	unset($FHOTWeaponStats);
	unset($SHOTWeaponStats);

	$handle = fopen($file, "r");
	//$lines = file($file);	
	$broken = explode("/", $file);
	$dir = $broken[1];
	$fileName = $broken[2];

	$timeStart = "";

	$victorTeam = "";
	$victorScore = "";
	$victorName = "";
	$victorPlayer = array();
	$loserTeam = "";
	$loserScore = "";
	$loserName = "";
	$loserPlayer = array();	

	$firstHalfT = array();
	$secondHalfT = array();
	$firstOTHalfT = array();
	$secondOTHalfT = array();
	$firstHalfCT = array();
	$secondHalfCT = array();
	$firstOTHalfCT = array();
	$secondOTHalfCT = array();

	$movieWorthy = array();
	$playersArray = array();
	$FHStats = array();
	$SHStats = array();
	$FHOTStats = array();
	$SHOTStats = array();

	$FHWeaponStats = array();
	$SHWeaponStats = array();
	$FHOTWeaponStats = array();
	$SHOTWeaponStats = array();

	$FinalStats = array();

	$inEvent = false;
	$half = 1;
	$i = 0;	
	$o = 0;

	$weaponsList = array ("ak47", "m4a1_silencer", "m4a1", "galilar", "famas", "awp", "p250", "glock", "hkp2000", "usp_silencer", "ump45", "p90", "bizon", "mp7", "nova", "knife", "elite", "fiveseven", "deagle", "tec9", "ssg08", "scar20", "aug", "sg553", "sg556", "g3sg1", "mac10", "mp9", "mag7", "negev", "m249", "sawedoff", "incgrenade", "flashbang", "smokegrenade", "hegrenade", "molotov", "decoy", "taser");
	$weaponsAssault = array ("ak47", "m4a1_silencer", "m4a1", "galilar", "famas", "aug", "sg553");
	$weaponsSniper = array ("awp", "ssg08", "scar20", "sg556", "g3sg1");
	$weaponsPistol = array ("p250", "glock", "hkp2000", "usp_silencer", "elite", "fiveseven", "deagle", "tec9");
	$weaponsSMG = array ("ump45", "p90", "bizon", "mp7", "mac10", "mp9");
	$weaponsHeavy = array ("nova", "mag7", "negev", "m249", "sawedoff");

if (!function_exists('SteamIDStringToSteamID')) {
	function SteamIDStringToSteamID($idString) {
	    //Test input steamId for invalid format

	    //Example SteamID: "STEAM_X:Y:ZZZZZZZZ"
	    $gameType = 0; //This is X.  It's either 0 or 1 depending on which game you are playing (CSS, L4D, TF2, etc)
	    $authServer = 0; //This is Y.  Some people have a 0, some people have a 1
	    $clientId = ''; //This is ZZZZZZZZ.

	    //Remove the "STEAM_"
	    $steamId = str_replace('STEAM_', '' ,$idString);

	    //Split steamId into parts
	    $parts = explode(':', $steamId);
	    $gameType = $parts[0];
	    $authServer = $parts[1];
	    $clientId = $parts[2];

	    //Calculate friendId
	    $result = bcadd((bcadd('76561197960265728', $authServer)), (bcmul($clientId, '2')));
	    return $result;
	}

	function &getStatsArrayName() {

		global $half, $FHStats, $SHStats;
		if ($half == 1){
			return  $FHStats;
		} elseif ($half == 2){
			return $SHStats;
		} elseif ($half == 3){
			return  $FHOTStats;
		} elseif ($half == 4){
			return $SHOTStats;
		}
	}
}

	while (!feof($handle)){
		$line = fgets($handle);
		$obj = json_decode($line, true);
		$event = $obj['event'];
		switch($event){
			case "log_start":
				$startTime =  strftime('%H:%M on the %d/%m/%Y',$obj['unixTime']);
				$gamebegin = strtotime($obj['timestamp']);
				$startTimestamp = $obj['timestamp'];
				$date_arr= explode(" ", $startTimestamp);
				$dateStart= $date_arr[0];
				$timeStart= $date_arr[1];
				break;
			case "log_end":
				$endTimestamp = $obj['timestamp'];
				$date_arr= explode(" ", $endTimestamp);
				$dateEnd= $date_arr[0];
				$timeEnd= $date_arr[1];
				break;
			case "live_on_3":
				$map = $obj['map'];
				$teamNameT = $obj['teams'][0]['name'];
				$teamNameCT = $obj['teams'][1]['name'];
				$warmodVersion = $obj['version'];
				$competition = "Team Virtual Sydney LAN";
				$event_name = "CS:GO";
				$servname = $obj['server'];
				break;		
			case "player_status":
				if ($obj['player']['uniqueId'] != "BOT"){
					if ($obj['player']['team'] != 1){
						$playerSteamId = $obj['player']['uniqueId'];

						$playersArray[$playerSteamId]['name'] = $obj['player']['name'];
						$playersArray[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$playersArray[$playerSteamId]['FHTeam'] = 0;
						$playersArray[$playerSteamId]['SHTeam'] = 0;
						$playersArray[$playerSteamId]['FHOTTeam'] = 0;
						$playersArray[$playerSteamId]['SHOTTeam'] = 0;

						$FHStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$FHStats[$playerSteamId]['kills'] = 0;
						$FHStats[$playerSteamId]['assists'] = 0;
						$FHStats[$playerSteamId]['assists_tk'] = 0;
						$FHStats[$playerSteamId]['deaths'] = 0;
						$FHStats[$playerSteamId]['tk'] = 0;
						$FHStats[$playerSteamId]['kdr'] = 0;
						$FHStats[$playerSteamId]['fpr'] = 0;
						$FHStats[$playerSteamId]['dpr'] = 0;
						$FHStats[$playerSteamId]['totaldamage'] = 0;
						$FHStats[$playerSteamId]['totalshots'] = 0;
						$FHStats[$playerSteamId]['totalhits'] = 0;
						$FHStats[$playerSteamId]['accuracy'] = 0;
						$FHStats[$playerSteamId]['headshotstotal'] = 0;
						$FHStats[$playerSteamId]['headshots'] = 0;
						$FHStats[$playerSteamId]['clutchwon'] = 0;
						$FHStats[$playerSteamId]['clutchattempts'] = 0;
						$FHStats[$playerSteamId]['clutch'] = 0;
						$FHStats[$playerSteamId]['totalrounds'] = 0;
						$FHStats[$playerSteamId]['1k'] = 0;
						$FHStats[$playerSteamId]['2k'] = 0;
						$FHStats[$playerSteamId]['3k'] = 0;
						$FHStats[$playerSteamId]['4k'] = 0;
						$FHStats[$playerSteamId]['Ace'] = 0;

						$FHWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$FHOTWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						foreach ($weaponsList as &$wL){
							$FHWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
							$SHWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
							$FHOTWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
							$SHOTWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
						}
						$SHWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$SHOTWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];

						$SHStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$SHStats[$playerSteamId]['kills'] = 0;
						$SHStats[$playerSteamId]['assists'] = 0;
						$SHStats[$playerSteamId]['assists_tk'] = 0;
						$SHStats[$playerSteamId]['deaths'] = 0;
						$SHStats[$playerSteamId]['tk'] = 0;
						$SHStats[$playerSteamId]['kdr'] = 0;
						$SHStats[$playerSteamId]['fpr'] = 0;
						$SHStats[$playerSteamId]['dpr'] = 0;
						$SHStats[$playerSteamId]['totaldamage'] = 0;
						$SHStats[$playerSteamId]['totalshots'] = 0;
						$SHStats[$playerSteamId]['totalhits'] = 0;
						$SHStats[$playerSteamId]['accuracy'] = 0;
						$SHStats[$playerSteamId]['headshotstotal'] = 0;
						$SHStats[$playerSteamId]['headshots'] = 0;
						$SHStats[$playerSteamId]['clutchwon'] = 0;
						$SHStats[$playerSteamId]['clutchattempts'] = 0;
						$SHStats[$playerSteamId]['clutch'] = 0;
						$SHStats[$playerSteamId]['totalrounds'] = 0;
						$SHStats[$playerSteamId]['1k'] = 0;
						$SHStats[$playerSteamId]['2k'] = 0;
						$SHStats[$playerSteamId]['3k'] = 0;
						$SHStats[$playerSteamId]['4k'] = 0;
						$SHStats[$playerSteamId]['Ace'] = 0;

						$FHOTStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$FHOTStats[$playerSteamId]['kills'] = 0;
						$FHOTStats[$playerSteamId]['assists'] = 0;
						$FHOTStats[$playerSteamId]['assists_tk'] = 0;
						$FHOTStats[$playerSteamId]['deaths'] = 0;
						$FHOTStats[$playerSteamId]['tk'] = 0;
						$FHOTStats[$playerSteamId]['kdr'] = 0;
						$FHOTStats[$playerSteamId]['fpr'] = 0;
						$FHOTStats[$playerSteamId]['dpr'] = 0;
						$FHOTStats[$playerSteamId]['totaldamage'] = 0;
						$FHOTStats[$playerSteamId]['totalshots'] = 0;
						$FHOTStats[$playerSteamId]['totalhits'] = 0;
						$FHOTStats[$playerSteamId]['accuracy'] = 0;
						$FHOTStats[$playerSteamId]['headshotstotal'] = 0;
						$FHOTStats[$playerSteamId]['headshots'] = 0;
						$FHOTStats[$playerSteamId]['clutchwon'] = 0;
						$FHOTStats[$playerSteamId]['clutchattempts'] = 0;
						$FHOTStats[$playerSteamId]['clutch'] = 0;
						$FHOTStats[$playerSteamId]['totalrounds'] = 0;
						$FHOTStats[$playerSteamId]['1k'] = 0;
						$FHOTStats[$playerSteamId]['2k'] = 0;
						$FHOTStats[$playerSteamId]['3k'] = 0;
						$FHOTStats[$playerSteamId]['4k'] = 0;
						$FHOTStats[$playerSteamId]['Ace'] = 0;

						$SHOTStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
						$SHOTStats[$playerSteamId]['kills'] = 0;
						$SHOTStats[$playerSteamId]['assists'] = 0;
						$SHOTStats[$playerSteamId]['assists_tk'] = 0;
						$SHOTStats[$playerSteamId]['deaths'] = 0;
						$SHOTStats[$playerSteamId]['tk'] = 0;
						$SHOTStats[$playerSteamId]['kdr'] = 0;
						$SHOTStats[$playerSteamId]['fpr'] = 0;
						$SHOTStats[$playerSteamId]['dpr'] = 0;
						$SHOTStats[$playerSteamId]['totaldamage'] = 0;
						$SHOTStats[$playerSteamId]['totalshots'] = 0;
						$SHOTStats[$playerSteamId]['totalhits'] = 0;
						$SHOTStats[$playerSteamId]['accuracy'] = 0;
						$SHOTStats[$playerSteamId]['headshotstotal'] = 0;
						$SHOTStats[$playerSteamId]['headshots'] = 0;
						$SHOTStats[$playerSteamId]['clutchwon'] = 0;
						$SHOTStats[$playerSteamId]['clutchattempts'] = 0;
						$SHOTStats[$playerSteamId]['clutch'] = 0;
						$SHOTStats[$playerSteamId]['totalrounds'] = 0;
						$SHOTStats[$playerSteamId]['1k'] = 0;
						$SHOTStats[$playerSteamId]['2k'] = 0;
						$SHOTStats[$playerSteamId]['3k'] = 0;
						$SHOTStats[$playerSteamId]['4k'] = 0;
						$SHOTStats[$playerSteamId]['Ace'] = 0;

						if ($obj['player']['team'] == 2){
							$playersArray[$playerSteamId]['FHTeam'] = 2;
							$playersArray[$playerSteamId]['SHTeam'] = 3;
							$playersArray[$playerSteamId]['FHOTTeam'] = 3;
							$playersArray[$playerSteamId]['SHOTTeam'] = 2;
						} elseif ($obj['player']['team'] == 3){
							$playersArray[$playerSteamId]['FHTeam'] = 3;
							$playersArray[$playerSteamId]['SHTeam'] = 2;
							$playersArray[$playerSteamId]['FHOTTeam'] = 2;
							$playersArray[$playerSteamId]['SHOTTeam'] = 3;
						}		

						$i++;
						break;
					}
				}
			case "player_connect":
				$alreadyExists = false;
				foreach ($playersArray as &$player){
					if ($player['steamid'] == $obj['player']['uniqueId']){
						$alreadyExists = true;
					}
				}
				if (($alreadyExists == false) && ($obj['player']['uniqueId'] != "BOT")){
					$playerSteamId = $obj['player']['uniqueId'];
					$playersArray[$playerSteamId]['name'] = $obj['player']['name'];
					$playersArray[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$playersArray[$playerSteamId]['FHTeam'] = 0;
					$playersArray[$playerSteamId]['SHTeam'] = 0;
					$playersArray[$playerSteamId]['FHOTTeam'] = 0;
					$playersArray[$playerSteamId]['SHOTTeam'] = 0;




					$FHStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$FHStats[$playerSteamId]['kills'] = 0;
					$FHStats[$playerSteamId]['assists'] = 0;
					$FHStats[$playerSteamId]['assists_tk'] = 0;
					$FHStats[$playerSteamId]['deaths'] = 0;
					$FHStats[$playerSteamId]['tk'] = 0;
					$FHStats[$playerSteamId]['kdr'] = 0;
					$FHStats[$playerSteamId]['fpr'] = 0;
					$FHStats[$playerSteamId]['dpr'] = 0;
					$FHStats[$playerSteamId]['totaldamage'] = 0;
					$FHStats[$playerSteamId]['totalshots'] = 0;
					$FHStats[$playerSteamId]['totalhits'] = 0;
					$FHStats[$playerSteamId]['accuracy'] = 0;
					$FHStats[$playerSteamId]['headshotstotal'] = 0;
					$FHStats[$playerSteamId]['headshots'] = 0;
					$FHStats[$playerSteamId]['clutchwon'] = 0;
					$FHStats[$playerSteamId]['clutchattempts'] = 0;
					$FHStats[$playerSteamId]['clutch'] = 0;
					$FHStats[$playerSteamId]['totalrounds'] = 0;
					$FHStats[$playerSteamId]['1k'] = 0;
					$FHStats[$playerSteamId]['2k'] = 0;
					$FHStats[$playerSteamId]['3k'] = 0;
					$FHStats[$playerSteamId]['4k'] = 0;
					$FHStats[$playerSteamId]['Ace'] = 0;

					$FHWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$FHOTWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					foreach ($weaponsList as &$wL){
						$FHWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
						$SHWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
						$FHOTWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
						$SHOTWeaponStats[$playerSteamId][$wL] = array('shots' => 0, 'hits' => 0, 'kills' => 0, 'headshots' => 0, 'accuracy' => 0);
					}
					$SHWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$SHOTWeaponStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];

					$SHStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$SHStats[$playerSteamId]['kills'] = 0;
					$SHStats[$playerSteamId]['assists'] = 0;
					$SHStats[$playerSteamId]['assists_tk'] = 0;
					$SHStats[$playerSteamId]['deaths'] = 0;
					$SHStats[$playerSteamId]['tk'] = 0;
					$SHStats[$playerSteamId]['kdr'] = 0;
					$SHStats[$playerSteamId]['fpr'] = 0;
					$SHStats[$playerSteamId]['dpr'] = 0;
					$SHStats[$playerSteamId]['totaldamage'] = 0;
					$SHStats[$playerSteamId]['totalshots'] = 0;
					$SHStats[$playerSteamId]['totalhits'] = 0;
					$SHStats[$playerSteamId]['accuracy'] = 0;
					$SHStats[$playerSteamId]['headshotstotal'] = 0;
					$SHStats[$playerSteamId]['headshots'] = 0;
					$SHStats[$playerSteamId]['clutchwon'] = 0;
					$SHStats[$playerSteamId]['clutchattempts'] = 0;
					$SHStats[$playerSteamId]['clutch'] = 0;
					$SHStats[$playerSteamId]['totalrounds'] = 0;
					$SHStats[$playerSteamId]['1k'] = 0;
					$SHStats[$playerSteamId]['2k'] = 0;
					$SHStats[$playerSteamId]['3k'] = 0;
					$SHStats[$playerSteamId]['4k'] = 0;
					$SHStats[$playerSteamId]['Ace'] = 0;

					$FHOTStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$FHOTStats[$playerSteamId]['kills'] = 0;
					$FHOTStats[$playerSteamId]['assists'] = 0;
					$FHOTStats[$playerSteamId]['assists_tk'] = 0;
					$FHOTStats[$playerSteamId]['deaths'] = 0;
					$FHOTStats[$playerSteamId]['tk'] = 0;
					$FHOTStats[$playerSteamId]['kdr'] = 0;
					$FHOTStats[$playerSteamId]['fpr'] = 0;
					$FHOTStats[$playerSteamId]['dpr'] = 0;
					$FHOTStats[$playerSteamId]['totaldamage'] = 0;
					$FHOTStats[$playerSteamId]['totalshots'] = 0;
					$FHOTStats[$playerSteamId]['totalhits'] = 0;
					$FHOTStats[$playerSteamId]['accuracy'] = 0;
					$FHOTStats[$playerSteamId]['headshotstotal'] = 0;
					$FHOTStats[$playerSteamId]['headshots'] = 0;
					$FHOTStats[$playerSteamId]['clutchwon'] = 0;
					$FHOTStats[$playerSteamId]['clutchattempts'] = 0;
					$FHOTStats[$playerSteamId]['clutch'] = 0;
					$FHOTStats[$playerSteamId]['totalrounds'] = 0;
					$FHOTStats[$playerSteamId]['1k'] = 0;
					$FHOTStats[$playerSteamId]['2k'] = 0;
					$FHOTStats[$playerSteamId]['3k'] = 0;
					$FHOTStats[$playerSteamId]['4k'] = 0;
					$FHOTStats[$playerSteamId]['Ace'] = 0;

					$SHOTStats[$playerSteamId]['steamid'] = $obj['player']['uniqueId'];
					$SHOTStats[$playerSteamId]['kills'] = 0;
					$SHOTStats[$playerSteamId]['assists'] = 0;
					$SHOTStats[$playerSteamId]['assists_tk'] = 0;
					$SHOTStats[$playerSteamId]['deaths'] = 0;
					$SHOTStats[$playerSteamId]['tk'] = 0;
					$SHOTStats[$playerSteamId]['kdr'] = 0;
					$SHOTStats[$playerSteamId]['fpr'] = 0;
					$SHOTStats[$playerSteamId]['dpr'] = 0;
					$SHOTStats[$playerSteamId]['totaldamage'] = 0;
					$SHOTStats[$playerSteamId]['totalshots'] = 0;
					$SHOTStats[$playerSteamId]['totalhits'] = 0;
					$SHOTStats[$playerSteamId]['accuracy'] = 0;
					$SHOTStats[$playerSteamId]['headshotstotal'] = 0;
					$SHOTStats[$playerSteamId]['headshots'] = 0;
					$SHOTStats[$playerSteamId]['clutchwon'] = 0;
					$SHOTStats[$playerSteamId]['clutchattempts'] = 0;
					$SHOTStats[$playerSteamId]['clutch'] = 0;
					$SHOTStats[$playerSteamId]['totalrounds'] = 0;
					$SHOTStats[$playerSteamId]['1k'] = 0;
					$SHOTStats[$playerSteamId]['2k'] = 0;
					$SHOTStats[$playerSteamId]['3k'] = 0;
					$SHOTStats[$playerSteamId]['4k'] = 0;
					$SHOTStats[$playerSteamId]['Ace'] = 0;
					$i++;
				}
				break;
			case "player_team":
				if ($half == 1){
					if ($obj['oldTeam'] == 0){
						if ($obj['newTeam'] == 2){
							$playersArray[$obj['player']['uniqueId']]['FHTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['SHTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 2;

						} elseif ($obj['newTeam'] == 3){
							$playersArray[$obj['player']['uniqueId']]['FHTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['SHTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 3;
						}
					}
				} elseif ($half == 2){
					if ($obj['oldTeam'] == 0){
						if ($obj['newTeam'] == 3){
							$playersArray[$obj['player']['uniqueId']]['FHTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['SHTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 2;
						} elseif ($obj['newTeam'] == 2){
							$playersArray[$obj['player']['uniqueId']]['FHTeam'] = 3;
							$playersArray[$obj['player']['uniqueId']]['SHTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 2;
							$playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 3;
						}
					}
				} elseif ($half == 3){
                                        if ($obj['oldTeam'] == 0){
                                                if ($obj['newTeam'] == 3){
                                                        $playersArray[$obj['player']['uniqueId']]['FHTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['SHTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 2;
                                                } elseif ($obj['newTeam'] == 2){
                                                        $playersArray[$obj['player']['uniqueId']]['FHTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['SHTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 3;
                                                }
                                        }
                                } elseif ($half == 4){
                                        if ($obj['oldTeam'] == 0){
                                                if ($obj['newTeam'] == 2){
                                                        $playersArray[$obj['player']['uniqueId']]['FHTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['SHTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 2;
                                                } elseif ($obj['newTeam'] == 3){
                                                        $playersArray[$obj['player']['uniqueId']]['FHTeam'] = 3;
                                                        $playersArray[$obj['player']['uniqueId']]['SHTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['FHOTTeam'] = 2;
                                                        $playersArray[$obj['player']['uniqueId']]['SHOTTeam'] = 3;
                                                }
                                        }
                                }
				break;
			case "half_time":
				$half = 2;
				if ($obj['teams'][0]['team'] == 2){
					$firstHalfTScore = $obj['teams'][0]['score'];
					$firstHalfCTScore = $obj['teams'][1]['score'];

				} else {
					$firstHalfTScore = $obj['teams'][1]['score'];
					$firstHalfCTScore = $obj['teams'][0]['score'];
				}

				break;
			case "over_time":
				$half = 3;
				if ($obj['teams'][0]['team'] == 2){
					$secondHalfTScore = $obj['teams'][0]['score'];
					$secondHalfCTScore = $obj['teams'][1]['score'];

				} else {
					$secondHalfTScore = $obj['teams'][1]['score'];
					$secondHalfCTScore = $obj['teams'][0]['score'];
				}

				break;
			case "over_half_time":
				$half = 4;
				if ($obj['teams'][0]['team'] == 2){
					$firstOTHalfTScore = $obj['teams'][0]['score'];
					$firstOTHalfCTScore = $obj['teams'][1]['score'];

				} else {
					$firstOTHalfTScore = $obj['teams'][1]['score'];
					$firstOTHalfCTScore = $obj['teams'][0]['score'];
				}

				break;
			case "ready_system":
				if ($obj['enabled'] == false){
					if ($half == 1){
						foreach ($FHStats as &$player){
							$player['kills'] = 0;
							$player['assists'] = 0;
							$player['assists_tk'] = 0;
							$player['deaths'] = 0;
							$player['tk'] = 0;
							$player['kdr'] = 0;
							$player['fpr'] = 0;
							$player['dpr'] = 0;
							$player['totaldamage'] = 0;
							$player['totalshots'] = 0;
							$player['totalhits'] = 0;
							$player['accuracy'] = 0;
							$player['headshotstotal'] = 0;
							$player['headshots'] = 0;
							$player['clutchwon'] = 0;
							$player['clutchattempts'] = 0;
							$player['clutch'] = 0;
							$player['totalrounds'] = 0;
							$player['1k'] = 0;
							$player['2k'] = 0;
							$player['3k'] = 0;
							$player['4k'] = 0;
							$player['Ace'] = 0;
						}
					} elseif ($half == 2) {
						foreach ($SHStats as &$player){		
							$player['kills'] = 0;
							$player['assists'] = 0;
							$player['assists_tk'] = 0;
							$player['deaths'] = 0;
							$player['tk'] = 0;
							$player['kdr'] = 0;
							$player['fpr'] = 0;
							$player['dpr'] = 0;
							$player['totaldamage'] = 0;
							$player['totalshots'] = 0;
							$player['totalhits'] = 0;
							$player['accuracy'] = 0;
							$player['headshotstotal'] = 0;
							$player['headshots'] = 0;
							$player['clutchwon'] = 0;
							$player['clutchattempts'] = 0;
							$player['clutch'] = 0;
							$player['totalrounds'] = 0;
							$player['1k'] = 0;
							$player['2k'] = 0;
							$player['3k'] = 0;
							$player['4k'] = 0;
							$player['Ace'] = 0;
						}
					} elseif ($half == 3) {
						foreach ($FHOTStats as &$player){		
							$player['kills'] = 0;
							$player['assists'] = 0;
							$player['assists_tk'] = 0;
							$player['deaths'] = 0;
							$player['tk'] = 0;
							$player['kdr'] = 0;
							$player['fpr'] = 0;
							$player['dpr'] = 0;
							$player['totaldamage'] = 0;
							$player['totalshots'] = 0;
							$player['totalhits'] = 0;
							$player['accuracy'] = 0;
							$player['headshotstotal'] = 0;
							$player['headshots'] = 0;
							$player['clutchwon'] = 0;
							$player['clutchattempts'] = 0;
							$player['clutch'] = 0;
							$player['totalrounds'] = 0;
							$player['1k'] = 0;
							$player['2k'] = 0;
							$player['3k'] = 0;
							$player['4k'] = 0;
							$player['Ace'] = 0;
						}
					} elseif ($half == 4) {
						foreach ($SHOTStats as &$player){		
							$player['kills'] = 0;
							$player['assists'] = 0;
							$player['assists_tk'] = 0;
							$player['deaths'] = 0;
							$player['tk'] = 0;
							$player['kdr'] = 0;
							$player['fpr'] = 0;
							$player['dpr'] = 0;
							$player['totaldamage'] = 0;
							$player['totalshots'] = 0;
							$player['totalhits'] = 0;
							$player['accuracy'] = 0;
							$player['headshotstotal'] = 0;
							$player['headshots'] = 0;
							$player['clutchwon'] = 0;
							$player['clutchattempts'] = 0;
							$player['clutch'] = 0;
							$player['totalrounds'] = 0;
							$player['1k'] = 0;
							$player['2k'] = 0;
							$player['3k'] = 0;
							$player['4k'] = 0;
							$player['Ace'] = 0;
						}
					}
				}
				break;
			case "player_clutch":
				if ($half == 1){
					$st =& $FHStats;
				} elseif ($half == 2) {
					$st =& $SHStats;
				} elseif ($half == 3) {
					$st =& $FHOTStats;
				} elseif ($half == 4) {
					$st =& $SHOTStats;
				}
				foreach ($st as &$player){
					if ($obj['player']['uniqueId'] == $player['steamid']){
						$won = $obj['won'];
						if ($won > 0){
							$player['clutchwon'] = ($player['clutchwon']+1);
							// Calculate tickrate
							$movieWorthyTime = strtotime($obj['timestamp'])-105;
							$movieWorthyTick = ($movieWorthyTime - $gamebegin) * 128;// SHOULDN'T HARDCODE TICKRATE
							if ($movieWorthyTick < 0){
								$movieWorthyTick = "First couple of rounds";
							}									
							$movieWorthyName = $obj['player']['name'];
							$movieWorthyDetails = "Clutch won - 1v".$obj['versus'];
							$movieWorthy[$o]['movieWorthyName'] = $movieWorthyName;
							$movieWorthy[$o]['movieWorthyTick'] = $movieWorthyTick;
							$movieWorthy[$o]['movieWorthyDetails'] = $movieWorthyDetails;
							//print_r($movieWorthy);
						}
						$player['clutchattempts'] = $player['clutchattempts']+1;
						$player['clutch'] = round(($player['clutchwon']/$player['clutchattempts'])*100,2) . '%';
					}
				}
				break;
			case "player_name":
				foreach ($playersArray as &$player){
					if (in_array($obj['player']['uniqueId'],$player)){
						if ($obj['player']['uniqueId'] == $player['steamid']){
							$player['name'] = $obj['newName'];
						}
					}
				}
				break;
			case "weapon_stats":
				if ($half == 1){
					$st =& $FHWeaponStats;
				} elseif ($half == 2) {
					$st =& $SHWeaponStats;
				} elseif ($half == 3) {
					$st =& $FHOTWeaponStats;
				} elseif ($half == 4) {
					$st =& $SHOTWeaponStats;
				}
				foreach ($st as &$player){
					if ($obj['player']['uniqueId'] == $player['steamid']){
					$weapon = $obj['weapon'];
					$player[$weapon]['shots'] = $player[$weapon]['shots'] + $obj['shots'];
					$player[$weapon]['hits'] = $player[$weapon]['hits'] + $obj['hits'];
					$player[$weapon]['kills'] = $player[$weapon]['kills'] + $obj['kills'];
					$player[$weapon]['headshots'] = $player[$weapon]['headshots'] + $obj['headshots'];
					}
				}
				break;
			case "player_hurt":
				if ($half == 1){
					$st =& $FHStats;
				} elseif ($half == 2) {
					$st =& $SHStats;
				} elseif ($half == 3) {
					$st =& $FHOTStats;
				} elseif ($half == 4) {
					$st =& $SHOTStats;
				}
				$attacker = $obj['attacker']['uniqueId'];
				$victim = $obj['victim']['uniqueId'];
				$hpRemain = $obj['victim']['health'];
				foreach ($st as &$player){
					if ($obj['attacker']['uniqueId'] == $player['steamid']){
						if ($hpRemain < 0){
							$actualDmg = ($obj['damage'] - abs($hpRemain));
							$player['totaldamage'] = $player['totaldamage'] + $actualDmg;
						} else {
							$player['totaldamage'] = $player['totaldamage'] + $obj['damage'];
						}
					}
				}
				break;
			case "player_death":
				//$st = getStatsArrayName();
				if ($half == 1){
					$st =& $FHStats;
				} elseif ($half == 2) {
					$st =& $SHStats;
				} elseif ($half == 3) {
					$st =& $FHOTStats;
				} elseif ($half == 4) {
					$st =& $SHOTStats;
				}

				$attacker = $obj['attacker']['uniqueId'];
				$victim = $obj['victim']['uniqueId'];
				$assister = $obj['assister']['uniqueId'];
				$attackerTeam = $obj['attacker']['team'];
				$victimTeam = $obj['victim']['team'];
				$victimTeam = $obj['victim']['team'];
				$assisterTeam = $obj['assister']['team'];
				foreach ($st as &$player){
					if (in_array($attacker,$player)){
						if ($obj['attacker']['uniqueId'] == $player['steamid']){
							$player['kills'] = $player['kills'] + 1;
							if ($player['deaths'] > 0){
								$player['kdr'] = round($player['kills'] / $player['deaths'], 2);
							} else {
								$player['kdr'] = round($player['kills'] / 1, 2);
							}
							if ($obj['attacker']['team'] == $obj['victim']['team']){
								$player['tk'] = $player['tk'] + 1;
							} 
						}
					}
					if (in_array($victim,$player)){
						if ($obj['victim']['uniqueId'] == $player['steamid']){
							$player['deaths'] = $player['deaths'] + 1;
							$player['kdr'] = round($player['kills'] / $player['deaths'], 2);
						}
					}
					if (in_array($assister,$player)){
						if ($obj['assister']['uniqueId'] == $player['steamid']){
							if ($obj['assister']['team'] == $obj['victim']['team']){
								$player['assists_tk'] = $player['assists_tk'] + 1;
							} else {
								$player['assists'] = $player['assists'] + 1;
							}
						}
					}
				}				
				break;
			case "full_time":
			case "over_full_time":
				$ctTeam = $obj['teams'][0]['team'];
				$ctScore = $obj['teams'][0]['score'];
				if ($obj['teams'][0]['name'] = "Terrorists"){
					$ctName = "Counter-Terrorists";
				}else{
					$ctName = $obj['teams'][0]['name'];
				}
				$tTeam = $obj['teams'][1]['team'];
				$tScore = $obj['teams'][1]['score'];
				if ($obj['teams'][1]['name'] = "Counter-Terrorists"){
					$tName = "Terrorists";
				}else{
					$tName = $obj['teams'][1]['name'];
				}

				if (($obj['teams'][0]['score'] > 15) && ($obj['teams'][0]['score'] > $obj['teams'][1]['score'])){
					$victorTeam = $obj['teams'][0]['team'];
					$victorScore = $obj['teams'][0]['score'];
					$victorName = $obj['teams'][0]['name'];
					$loserTeam = $obj['teams'][1]['team'];
					$loserScore = $obj['teams'][1]['score'];
					$loserName = $obj['teams'][1]['name'];
				} elseif (($obj['teams'][0]['score'] < 15) && ($obj['teams'][0]['score'] < $obj['teams'][1]['score'])){
					$victorTeam = $obj['teams'][1]['team'];
					$victorScore = $obj['teams'][1]['score'];
					$victorName = $obj['teams'][1]['name'];
					$loserTeam = $obj['teams'][0]['team'];
					$loserScore = $obj['teams'][0]['score'];
					$loserName = $obj['teams'][0]['name'];
				}  elseif ($obj['teams'][0]['score'] == 15 && $obj['teams'][1]['score'] == 15){
					$victorTeam = 0;
					$loserTeam = 0;
					$victorScore = $obj['teams'][0]['score'];
					$loserScore = $obj['teams'][1]['score'];
					$victorName = $obj['teams'][0]['name'];
					$loserName = $obj['teams'][1]['name'];
				}

				//$totalrounds = $victorScore + $loserScore;
				//$st = getStatsArrayName();

				foreach ($playersArray as &$player){
					if ((array_key_exists($player['steamid'], $FHStats)) && (array_key_exists($player['steamid'], $FHStats))){
						$steamIdUse = $player['steamid'];
						$player['kills'] = $FHStats[$steamIdUse]['kills'] + $SHStats[$steamIdUse]['kills'] + $FHOTStats[$steamIdUse]['kills'] + $SHOTStats[$steamIdUse]['kills'];
						$player['assists'] = $FHStats[$steamIdUse]['assists'] + $SHStats[$steamIdUse]['assists'] + $FHOTStats[$steamIdUse]['assists'] + $SHOTStats[$steamIdUse]['assists'];
						$player['assists_tk'] = $FHStats[$steamIdUse]['assists_tk'] + $SHStats[$steamIdUse]['assists_tk'] + $FHOTStats[$steamIdUse]['assists_tk'] + $SHOTStats[$steamIdUse]['assists_tk'];
						$player['deaths'] = $FHStats[$steamIdUse]['deaths'] + $SHStats[$steamIdUse]['deaths'] + $FHOTStats[$steamIdUse]['deaths'] + $SHOTStats[$steamIdUse]['deaths'];	
						$player['tk'] = $FHStats[$steamIdUse]['tk'] + $SHStats[$steamIdUse]['tk'] + $FHOTStats[$steamIdUse]['tk'] + $SHOTStats[$steamIdUse]['tk'];	
						$player['totalrounds'] = $FHStats[$steamIdUse]['totalrounds'] + $SHStats[$steamIdUse]['totalrounds'] + $FHOTStats[$steamIdUse]['totalrounds'] + $SHOTStats[$steamIdUse]['totalrounds'];	
						$player['totaldamage'] = $FHStats[$steamIdUse]['totaldamage'] + $SHStats[$steamIdUse]['totaldamage'] + $FHOTStats[$steamIdUse]['totaldamage'] + $SHOTStats[$steamIdUse]['totaldamage'];
						$player['totalhits'] = $FHStats[$steamIdUse]['totalhits'] + $SHStats[$steamIdUse]['totalhits'] + $FHOTStats[$steamIdUse]['totalhits'] + $SHOTStats[$steamIdUse]['totalhits'];
						$player['totalshots'] = $FHStats[$steamIdUse]['totalshots'] + $SHStats[$steamIdUse]['totalshots'] + $FHOTStats[$steamIdUse]['totalshots'] + $SHOTStats[$steamIdUse]['totalshots'];
						$player['headshotstotal'] = $FHStats[$steamIdUse]['headshotstotal'] + $SHStats[$steamIdUse]['headshotstotal'] + $FHOTStats[$steamIdUse]['headshotstotal'] + $SHOTStats[$steamIdUse]['headshotstotal'];
						$player['1k'] = $FHStats[$steamIdUse]['1k'] + $SHStats[$steamIdUse]['1k'] + $FHOTStats[$steamIdUse]['1k'] + $SHOTStats[$steamIdUse]['1k'];
						$player['2k'] = $FHStats[$steamIdUse]['2k'] + $SHStats[$steamIdUse]['2k'] + $FHOTStats[$steamIdUse]['2k'] + $SHOTStats[$steamIdUse]['2k'];
						$player['3k'] = $FHStats[$steamIdUse]['3k'] + $SHStats[$steamIdUse]['3k'] + $FHOTStats[$steamIdUse]['3k'] + $SHOTStats[$steamIdUse]['3k'];
						$player['4k'] = $FHStats[$steamIdUse]['4k'] + $SHStats[$steamIdUse]['4k'] + $FHOTStats[$steamIdUse]['4k'] + $SHOTStats[$steamIdUse]['4k'];
						$player['Ace'] = $FHStats[$steamIdUse]['Ace'] + $SHStats[$steamIdUse]['Ace'] + $FHOTStats[$steamIdUse]['Ace'] + $SHOTStats[$steamIdUse]['Ace'];
						$player['clutchattempts'] = $FHStats[$steamIdUse]['clutchattempts'] + $SHStats[$steamIdUse]['clutchattempts'] + $FHOTStats[$steamIdUse]['clutchattempts'] + $SHOTStats[$steamIdUse]['clutchattempts'];
						$player['clutchwon'] = $FHStats[$steamIdUse]['clutchwon'] + $SHStats[$steamIdUse]['clutchwon'] + $FHOTStats[$steamIdUse]['clutchwon'] + $SHOTStats[$steamIdUse]['clutchwon'];
						foreach ($weaponsList as &$wL){
							$player[$wL]['shots'] = $FHWeaponStats[$steamIdUse][$wL]['shots'] + $SHWeaponStats[$steamIdUse][$wL]['shots'] + $FHOTWeaponStats[$steamIdUse][$wL]['shots'] + $SHOTWeaponStats[$steamIdUse][$wL]['shots'];
							$player[$wL]['hits'] = $FHWeaponStats[$steamIdUse][$wL]['hits'] + $SHWeaponStats[$steamIdUse][$wL]['hits'] + $FHOTWeaponStats[$steamIdUse][$wL]['hits'] + $SHOTWeaponStats[$steamIdUse][$wL]['hits'];
							$player[$wL]['kills'] = $FHWeaponStats[$steamIdUse][$wL]['kills'] + $SHWeaponStats[$steamIdUse][$wL]['kills'] + $FHOTWeaponStats[$steamIdUse][$wL]['kills'] + $SHOTWeaponStats[$steamIdUse][$wL]['kills'];
							$player[$wL]['headshots'] = $FHWeaponStats[$steamIdUse][$wL]['headshots'] + $SHWeaponStats[$steamIdUse][$wL]['headshots'] + $FHOTWeaponStats[$steamIdUse][$wL]['headshots'] + $SHOTWeaponStats[$steamIdUse][$wL]['headshots'];
						}

						$player['sniper']['shots'] = 0;
						$player['sniper']['hits'] = 0;
						$player['sniper']['kills'] = 0;
						$player['sniper']['headshots'] = 0;
						foreach ($weaponsSniper as &$wS){
							$player['sniper']['shots'] = $player['sniper']['shots'] + $player[$wS]['shots'];
							$player['sniper']['hits'] = $player['sniper']['hits'] + $player[$wS]['hits'];
							$player['sniper']['kills'] = $player['sniper']['kills'] + $player[$wS]['kills'];
							$player['sniper']['headshots'] = $player['sniper']['headshots'] + $player[$wS]['headshots'];
						}

						$player['assault']['shots'] = 0;
						$player['assault']['hits'] = 0;
						$player['assault']['kills'] = 0;
						$player['assault']['headshots'] = 0;
						foreach ($weaponsAssault as &$wS){
							$player['assault']['shots'] = $player['assault']['shots'] + $player[$wS]['shots'];
							$player['assault']['hits'] = $player['assault']['hits'] + $player[$wS]['hits'];
							$player['assault']['kills'] = $player['assault']['kills'] + $player[$wS]['kills'];
							$player['assault']['headshots'] = $player['assault']['headshots'] + $player[$wS]['headshots'];
						}

						$player['pistol']['shots'] = 0;
						$player['pistol']['hits'] = 0;
						$player['pistol']['kills'] = 0;
						$player['pistol']['headshots'] = 0;
						foreach ($weaponsPistol as &$wS){
							$player['pistol']['shots'] = $player['pistol']['shots'] + $player[$wS]['shots'];
							$player['pistol']['hits'] = $player['pistol']['hits'] + $player[$wS]['hits'];
							$player['pistol']['kills'] = $player['pistol']['kills'] + $player[$wS]['kills'];
							$player['pistol']['headshots'] = $player['pistol']['headshots'] + $player[$wS]['headshots'];
						}

						$player['smg']['shots'] = 0;						
						$player['smg']['hits'] = 0;
						$player['smg']['kills'] = 0;
						$player['smg']['headshots'] = 0;
						foreach ($weaponsSMG as &$wS){					
							$player['smg']['shots'] = $player['smg']['shots'] + $player[$wS]['shots'];
							$player['smg']['hits'] = $player['smg']['hits'] + $player[$wS]['hits'];
							$player['smg']['kills'] = $player['smg']['kills'] + $player[$wS]['kills'];
							$player['smg']['headshots'] = $player['smg']['headshots'] + $player[$wS]['headshots'];
						}

						$player['heavy']['shots'] = 0;						
						$player['heavy']['hits'] = 0;
						$player['heavy']['kills'] = 0;
						$player['heavy']['headshots'] = 0;
						foreach ($weaponsHeavy as &$wS){					
							$player['heavy']['shots'] = $player['heavy']['shots'] + $player[$wS]['shots'];
							$player['heavy']['hits'] = $player['heavy']['hits'] + $player[$wS]['hits'];
							$player['heavy']['kills'] = $player['heavy']['kills'] + $player[$wS]['kills'];
							$player['heavy']['headshots'] = $player['heavy']['headshots'] + $player[$wS]['headshots'];
						}			
					}
				}

				foreach ($playersArray as &$player){
					if ($player['totalrounds'] > 0){
						$player['dpr'] = round($player['totaldamage']/$player['totalrounds'],2);
						$player['fpr'] = round($player['kills']/$player['totalrounds'],2);
					} else {
						$player['dpr'] = 0;
						$player['fpr'] = 0;
					}
					if ($player['deaths'] > 0){
						$player['kdr'] = round($player['kills']/$player['deaths'],2);
					} else {
						$player['kdr'] = round($player['kills']/1,2);
					}					

					if ($player['totalshots'] > 0){
						$player['accuracy'] = round(($player['totalhits']/$player['totalshots'])*100,2) . "%";
					} else {
						$player['accuracy'] = "0%";
					}

					if ($player['sniper']['shots'] > 0){
						$player['sniper']['accuracy'] = round(($player['sniper']['hits']/$player['sniper']['shots'])*100,2) . "%";
					} else {
						$player['sniper']['accuracy'] = "0%";
					}
					if ($player['assault']['shots'] > 0){
						$player['assault']['accuracy'] = round(($player['assault']['hits']/$player['assault']['shots'])*100,2) . "%";
					} else {
						$player['assault']['accuracy'] = "0%";
					}
					if ($player['pistol']['shots'] > 0){
						$player['pistol']['accuracy'] = round(($player['pistol']['hits']/$player['pistol']['shots'])*100,2) . "%";
					} else {
						$player['pistol']['accuracy'] = "0%";
					}
					if ($player['smg']['shots'] > 0){
						$player['smg']['accuracy'] = round(($player['smg']['hits']/$player['smg']['shots'])*100,2) . "%";
					} else {
						$player['smg']['accuracy'] = "0%";
					}
					if ($player['heavy']['shots'] > 0){
						$player['heavy']['accuracy'] = round(($player['heavy']['hits']/$player['heavy']['shots'])*100,2) . "%";
					} else {
						$player['heavy']['accuracy'] = "0%";
					}

					if ($player['kills'] > 0){
						$player['headshots'] = round(($player['headshotstotal']/$player['kills'])*100,2) . "%";
					} else {
						$player['headshots'] = "0%";
					}
					if ($player['clutchattempts'] > 0){
						$player['clutch'] = round(($player['clutchwon']/$player['clutchattempts'])*100,2) . '%';
					} else {
						$player['clutch'] = "0%";
					}
					foreach ($weaponsList as &$wL){
						if ($player[$wL]['shots'] > 0){
							$player[$wL]['accuracy'] = round(($player[$wL]['hits']/$player[$wL]['shots'])*100,2) . "%";
						} else {
							$player[$wL]['accuracy'] = "0%";
						}
					}			
				}				
				break;
			case "round_stats":
				if ($half == 1){
					$st =& $FHStats;
				} elseif ($half == 2) {
					$st =& $SHStats;
				} elseif ($half == 3) {
					$st =& $FHOTStats;
				} elseif ($half == 4) {
					$st =& $SHOTStats;
				}

				foreach ($st as &$player){
					if ($obj['player']['uniqueId'] == $player['steamid']){
						$kills = $obj['kills'];
						$player['totalrounds'] = $player['totalrounds'] + 1;
						switch ($kills){
							case 0:
								break;
							case 1:
								$player['1k'] = $player['1k'] + 1;
								break;
							case 2:
								$player['2k'] = $player['2k'] + 1;
								break;
							case 3:
								$player['3k'] = $player['3k'] + 1;
								$movieWorthyTime = strtotime($obj['timestamp'])-105;
								$movieWorthyTick = ($movieWorthyTime - $gamebegin) * 128;// SHOULDN'T HARDCODE TICKRATE
								if ($movieWorthyTick < 0){
									$movieWorthyTick = "First couple of rounds";
								}										
								$movieWorthyName = $obj['player']['name'];
								$movieWorthyDetails = "3 kills - ".$obj['headshots']." headshot(s)";
								$movieWorthy[$o]['movieWorthyName'] = $movieWorthyName;
								$movieWorthy[$o]['movieWorthyTick'] = $movieWorthyTick;
								$movieWorthy[$o]['movieWorthyDetails'] = $movieWorthyDetails;
								break;		
							case 4:
								$player['4k'] = $player['4k'] + 1;
								$movieWorthyTime = strtotime($obj['timestamp'])-105;
								$movieWorthyTick = ($movieWorthyTime - $gamebegin) * 128;// SHOULDN'T HARDCODE TICKRATE
								if ($movieWorthyTick < 0){
									$movieWorthyTick = "First couple of rounds";
								}
								$movieWorthyName = $obj['player']['name'];
								$movieWorthyDetails = "4 kills - ".$obj['headshots']." headshot(s)";
								$movieWorthy[$o]['movieWorthyName'] = $movieWorthyName;
								$movieWorthy[$o]['movieWorthyTick'] = $movieWorthyTick;
								$movieWorthy[$o]['movieWorthyDetails'] = $movieWorthyDetails;
								break;	
							case 5:
								$player['Ace'] = $player['Ace'] + 1;
								$movieWorthyTime = strtotime($obj['timestamp'])-105;
								$movieWorthyTick = ($movieWorthyTime - $gamebegin) * 128;// SHOULDN'T HARDCODE TICKRATE
								if ($movieWorthyTick < 0){
									$movieWorthyTick = "First couple of rounds";
								}
								$movieWorthyName = $obj['player']['name'];
								$movieWorthyDetails = "ACE, DUDE, ACE - ".$obj['headshots']." headshot(s)";
								$movieWorthy[$o]['movieWorthyName'] = $movieWorthyName;
								$movieWorthy[$o]['movieWorthyTick'] = $movieWorthyTick;
								$movieWorthy[$o]['movieWorthyDetails'] = $movieWorthyDetails;
								break;							
						}

						//Calculate Accuracy
						$player['totalshots'] = $player['totalshots'] + $obj['shots'];
						$player['totalhits'] = $player['totalhits'] + $obj['hits'];
						$player['headshotstotal'] = $player['headshotstotal'] + $obj['headshots'];
					}
					$o++;
				}
				break;
		}//end of switch

	}//end of while
	fclose($handle);
	$rename = basename($file);
	rename($file, "/home/jake/logs/old/$rename");
//stats to SQL
/*	$dbhost = 'ip:port';
	$dbuser = 'username';
	$dbpass = 'password';
	$conn = mysql_connect($dbhost, $dbuser, $dbpass);
	if(!$conn)
	{
		die('Could not connect: ' . mysql_error());
	}

	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0)){
			$sql_player = "INSERT INTO player (name,steamid,kills,assists,assists_tk,deaths,tk,totalrounds,totaldamage,totalhits,totalshots,headshotstotal,1k,2k,3k,4k,Ace) VALUES ('{$player['name']}','{$player['steamid']}','{$player['kills']}','{$player['assists']}','{$player['assists_tk']}','{$player['deaths']}','{$player['tk']}','{$player['totalrounds']}','{$player['totaldamage']}','{$player['totalhits']}','{$player['totalshots']}','{$player['headshotstotal']}','{$player['1k']}','{$player['2k']}','{$player['3k']}','{$player['4k']}','{$player['Ace']}')";
		
			mysql_select_db('database name');
			$retval = mysql_query( $sql_player, $conn);
			if(!$retval)
			{
				die('Could not enter data: ' . mysql_error());
			}
		}
	}
	echo "Entered data successfully\n";
	mysql_close($conn);*/
//create static stats site
	$_ = "_";
	$hScore = "$ctScore-$tScore";
	$maps = str_replace("_", "-", $map);
	if ($competition == "WarMod BFG" || $competition == ""){
		$siteName = "$dateStart$_$timeStart$_$maps$_$teamNameCT$_$hScore$_$teamNameT.html";
	}else{
		$siteName = "$dateStart$_$timeStart$_$competition$_$event_name$_$maps$_$teamNameCT$_$hScore$_$teamNameT.html";
	}
	$siteNameFix = str_replace(":", "-", $siteName);
	$fp = fopen("/home/jake/$siteNameFix", "w");

	fwrite($fp, "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n");
	fwrite($fp, "<html>\n");
	fwrite($fp, "<head>\n");
	fwrite($fp, "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\">\n");
	if ($competition == "WarMod BFG" || $competition == ""){
		fwrite($fp, "<title>SYD LAN - $teamNameCT vs $teamNameT</title>\n");
	}else{
		fwrite($fp, "<title>SYD LAN - $competition $event_name - $teamNameCT vs $teamNameT</title>\n");
	}
	fwrite($fp, "<link rel=\"shortcut icon\" href=\"http://bfg-esports.com/img/favicon.ico\">\n");
	fwrite($fp, "<LINK href=\"http://ladder.teamvirtual.com.au/css/statslayout.css\" rel=\"stylesheet\" type=\"text/css\">\n");
	fwrite($fp, "<script type=\"text/javascript\" src=\"http://ladder.teamvirtual.com.au/js/sorttable.js\"></script>\n");
	fwrite($fp, "</head>\n");
	fwrite($fp, "<body>\n");
	fwrite($fp, "<div id=\"stats\">\n");
	fwrite($fp, "<div id=\"header\">\n");
	fwrite($fp, "<div id=\"headerContent\">\n");
	fwrite($fp, "<div class=\"headerdisplay\">\n");
	fwrite($fp, "<div id=\"logo\">\n");
	fwrite($fp, "<a href=\"\" title=\"Go to community index\" rel=\"home\" accesskey=\"1\" class=\'left\'><img src=\"\"></a>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "<div id=\"primary_nav\" class=\"clearfix\">\n");
	fwrite($fp, "<ul class=\"ipsList_inline\" id=\"community_app_menu\">\n");
	fwrite($fp, "</ul>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "<div id=\"base\">\n");
	fwrite($fp, "<div id=\"main\" class=\"clearfix\">\n");
	fwrite($fp, "<div style=\"width:100%;margin:0;padding:0;border:none;\">\n");
	fwrite($fp, "<br>\n");
	fwrite($fp, "<div style=\"float:left;width:50%;\">\n");
	fwrite($fp, "<table style=\"width: 60%\">\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th>Summary:</th>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<td>Competition: $competition : $event_name</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	fwrite($fp, "<td>Match: $teamNameCT vs $teamNameT</td>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	fwrite($fp, "<td>Server: $servname</td>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<td>Warmod version: $warmodVersion</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<td>Map: $map</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<td>Date: $dateStart</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<td>Time: $timeStart</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$difference = strtotime( $endTimestamp .' UTC' ) - strtotime( $startTimestamp .' UTC');
	$gametime = floor( $difference / 60 );
	$stringData = "<td>Match length: $gametime minutes</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$totalscore = $tScore + $ctScore;
	$stringData = "<td>Rounds: $totalscore</td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	$file1 = basename($file, ".log");
	fwrite($fp, "<td>GOTV Demo: <a href=\"http://demos.teamvirtual.com.au/$file1.dem.bz2\" target=\"_blank\"><b>Download</b></a></td>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "\n");
	fwrite($fp, "</tbody></table>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "<div style=\"float:left;\">\n");
	fwrite($fp, "\n");
	fwrite($fp, "<table class=\"score\"><tbody><tr>\n");
	$stringData = "<td class=\"blue\" style=\"width: 50%\"><h1 class=\"left\">$teamNameCT</h1><h1 class=\"right\">$ctScore</h1></td>\n";
	fwrite($fp, $stringData);
	$stringData = "<td class=\"red\" style=\"width: 50%\"><h1 class=\"left\">$tScore</h1><h1 class=\"right\">$teamNameT</h1>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</td></tr></tbody></table>\n");
	fwrite($fp, "\n");
	fwrite($fp, "\n");
	fwrite($fp, "<table>\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr>\n");
	$stringData = "<th><img src=\"http://ladder.teamvirtual.com.au/img/$map.png\" alt=\"$map\"></th>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "</tbody></table>\n");
	$stringData = "<h3 style=\"text-align: center;\">$map</h3>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "<br style=\"clear:both;\">\n");
	$tTeamK = 0;
	$tTeamD = 0;
	$tTeamA = 0;
	$tTeamHS = 0;
	$tTeamTK = 0;
	$tTeamATA = 0;
	$tTeamKDR = 0;
	$tTeamKPR = 0;
	$tTeamDMG = 0;
	$tTeamDPR = 0;
	$tTeam1k = 0;
	$tTeam2k = 0;
	$tTeam3k = 0;
	$tTeam4k = 0;
	$tTeamAce = 0;
	$tTeamAC = 0;
	$tTeamTH = 0;
	$tTeamTS = 0;
	$ctTeamK = 0;
	$ctTeamD = 0;
	$ctTeamA = 0;
	$ctTeamHS = 0;
	$ctTeamTK = 0;
	$ctTeamATA = 0;
	$ctTeamKDR = 0;
	$ctTeamKPR = 0;
	$ctTeamDMG = 0;
	$ctTeamDPR = 0;
	$ctTeam1k = 0;
	$ctTeam2k = 0;
	$ctTeam3k = 0;
	$ctTeam4k = 0;
	$ctTeamAce = 0;
	$ctTeamAC = 0;
	$ctTeamTH = 0;
	$ctTeamTS = 0;
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 2)){
			$tTeamK = $tTeamK + $player['kills'];
			$tTeamD = $tTeamD + $player['deaths'];
			$tTeamA = $tTeamA + $player['assists'];
			$tTeamHS = $tTeamHS + $player['headshotstotal'];
			$tTeamTK = $tTeamTK + $player['tk'];
			$tTeamATA = $tTeamATA + $player['assists_tk'];
			$tTeamKDR = $tTeamKDR + $player['kdr'];
			$tTeamKPR = $tTeamKPR + $player['fpr'];
			$tTeamDMG = $tTeamDMG + $player['totaldamage'];
			$tTeamDPR = $tTeamDPR + $player['dpr'];
			$tTeam1k = $tTeam1k + $player['1k'];
			$tTeam2k = $tTeam2k + $player['2k'];
			$tTeam3k = $tTeam3k + $player['3k'];
			$tTeam4k = $tTeam4k + $player['4k'];
			$tTeamAce = $tTeamAce + $player['Ace'];
			$tTeamTH = $tTeamTH + $player['totalhits'];
			$tTeamTS = $tTeamTS + $player['totalshots'];
		}
	}
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 3)){
			$ctTeamK = $ctTeamK + $player['kills'];
			$ctTeamD = $ctTeamD + $player['deaths'];
			$ctTeamA = $ctTeamA + $player['assists'];
			$ctTeamHS = $ctTeamHS + $player['headshotstotal'];
			$ctTeamTK = $ctTeamTK + $player['tk'];
			$ctTeamATA = $ctTeamATA + $player['assists_tk'];
			$ctTeamKDR = $ctTeamKDR + $player['kdr'];
			$ctTeamKPR = $ctTeamKPR + $player['fpr'];
			$ctTeamDMG = $ctTeamDMG + $player['totaldamage'];
			$ctTeamDPR = $ctTeamDPR + $player['dpr'];
			$ctTeam1k = $ctTeam1k + $player['1k'];
			$ctTeam2k = $ctTeam2k + $player['2k'];
			$ctTeam3k = $ctTeam3k + $player['3k'];
			$ctTeam4k = $ctTeam4k + $player['4k'];
			$ctTeamAce = $ctTeamAce + $player['Ace'];
			$ctTeamTH = $ctTeamTH + $player['totalhits'];
			$ctTeamTS = $ctTeamTS + $player['totalshots'];
		}
	}
	$tTeamAC = round(($tTeamTH/$tTeamTS)*100,2) . "%";
	$ctTeamAC = round(($ctTeamTH/$ctTeamTS)*100,2) . "%";
	fwrite($fp, "<h2>Overall team stats:</h2>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<a onclick =\"javascript:ShowHide(\"HiddenDiv\")\" href=\"javascript:;\">Show/Hide</a>\n");
	fwrite($fp, "<div class=\"mid\" id=\"HiddenDiv\" style=\"DISPLAY: block\">\n");
	fwrite($fp, "<table class=\"sortable\">\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th>Team</th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">K<span>Total kills</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">D<span>Total deaths</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">A<span>Kill assists</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">HS<span>Headshots</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">TK<span>Team Kill</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">ATA<span>Assist Team Attack</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">KDR<span>Kill-death ratio</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">KPR<span>Kills per round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">DMG<span>Total damage</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">DPR<span>Damage per round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">1k<span>1 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">2k<span>2 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">3k<span>3 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">4k<span>4 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">Ace<span>5 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">AC%<span>Accuaracy: Hits/Shots</span></a></th>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	fwrite($fp, "<td><font class=\"Blue\">");
	fwrite($fp, $teamNameCT);
	fwrite($fp, "</font></td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamK);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamD);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamA);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamHS);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamTK);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamATA);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamKDR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamKPR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamDMG);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamDPR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeam1k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeam2k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeam3k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeam4k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $ctTeamAce);
	fwrite($fp, "</td>\n");
	$stringData = "<td><a class=\"tooltip\">$ctTeamAC<span>$ctTeamTH/$ctTeamTS</span></a></td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr><td><font class=\"Red\">");
	fwrite($fp, $teamNameT);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamK);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamD);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamA);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamHS);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamTK);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamATA);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamKDR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamKPR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamDMG);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamDPR);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeam1k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeam2k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeam3k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeam4k);
	fwrite($fp, "</td>\n");
	fwrite($fp, "<td>");
	fwrite($fp, $tTeamAce);
	$stringData  = "<td><a class=\"tooltip\">$tTeamAC<span>$tTeamTH/$tTeamTS</span></a></td>\n";
	fwrite($fp, $stringData);
	fwrite($fp, "</tr>\n");
	fwrite($fp, "</tbody></table>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<h2>Player stats:</h2>\n");
	fwrite($fp, "<a onclick =\"javascript:ShowHide(\"HiddenDiv1\")\" href=\"javascript:;\">Show/Hide</a>\n");
	fwrite($fp, "<div class=\"mid\" id=\"HiddenDiv1\" style=\"DISPLAY: block\" >\n");
	fwrite($fp, "<table class=\"sortable\">\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th>Player</th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">K<span>Total kills</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">D<span>Total deaths</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">A<span>Kill assists</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">HS<span>Headshots</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">TK<span>Team Kill</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">ATA<span>Assist Team Attack</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">KDR<span>Kill-death ratio</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">KPR<span>Kills per round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">DMG<span>Total damage</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">DPR<span>Damage per round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">1k<span>1 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">2k<span>2 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">3k<span>3 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">4k<span>4 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">Ace<span>5 kill round</span></a></th>\n");
	fwrite($fp, "<th><a class=\"tooltip\">AC%<span>Accuaracy: Hits/Shots</span></a></th>\n");
	fwrite($fp, "</tr>\n");
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 3)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Blue\">$value</font><span>$value1</span></a></b></td>";
			fwrite($fp, $stringData);
			$value = $player['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['deaths'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assists'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['headshotstotal'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['tk'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assists_tk'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['kdr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['fpr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['totaldamage'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['dpr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['1k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['2k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['3k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['4k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['Ace'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['totalhits'];
			$value1 = $player['totalshots'];
			$value2 = $player['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			fwrite($fp, "</tr>\n");

		}
	}
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 2)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Red\">$value</font><span>$value1</span></a></b></td>";
			fwrite($fp, $stringData);
			$value = $player['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['deaths'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assists'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['headshotstotal'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['tk'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assists_tk'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['kdr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['fpr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['totaldamage'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['dpr'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['1k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['2k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['3k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['4k'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['Ace'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['totalhits'];
			$value1 = $player['totalshots'];
			$value2 = $player['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			fwrite($fp, "</tr>\n");
		}
	}
	fwrite($fp, "</tbody></table>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<h2>Weapon stats:</h2>\n");
	fwrite($fp, "<a onclick =\"javascript:ShowHide(\"HiddenDiv2\")\" href=\"javascript:;\">Show/Hide</a>\n");
	fwrite($fp, "<div class=\"mid\" id=\"HiddenDiv2\" style=\"DISPLAY: block\" >\n");
	fwrite($fp, "<table class=\"sortable\">\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th></th>\n");
	fwrite($fp, "<th colspan=\"4\" style=\"text-align:center\"><a class=\"tooltip\">Assualt Rifle<span>AK47, M4A1, Famas, Gallil, Aug, SS</span></a></th>\n");
	fwrite($fp, "<th colspan=\"4\" style=\"text-align:center\"><a class=\"tooltip\">Sniper<span>AWP, SS, Auto</span></a></th>\n");
	fwrite($fp, "<th colspan=\"4\" style=\"text-align:center\"><a class=\"tooltip\">Pistol<span>Glock, P2000, USP, P250, ect.</span></a></th>\n");
	fwrite($fp, "<th colspan=\"4\" style=\"text-align:center\"><a class=\"tooltip\">SMG<span>Sub Machine Guns</span></a></th>\n");
	fwrite($fp, "<th colspan=\"4\" style=\"text-align:center\"><a class=\"tooltip\">Heavy<span>Shotguns & Machine Guns</span></a></th>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th>Player</th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">K<span>Total Kills</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">H<span>Total shots hit</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">HS<span>Head Shots</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">AC<span>Weapon accuracy</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">K<span>Total Kills</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">H<span>Total shots hit</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">HS<span>Head Shots</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">AC<span>Weapon accuracy</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">K<span>Total Kills</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">H<span>Total shots hit</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">HS<span>Head Shots</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">AC<span>Accuracy</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">K<span>Total Kills</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">H<span>Total shots hit</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">HS<span>Head Shots</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">AC<span>Accuracy</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">K<span>Total Kills</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">H<span>Total shots hit</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">HS<span>Head Shots</span></a></th>\n");
	fwrite($fp, "<th style=\"text-align:center\"><a class=\"tooltip\">AC<span>Accuracy</span></a></th>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tbody>\n");
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 3)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Blue\">$value</font><span>$value1</span></a></b></td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['hits'];
			$value1 = $player['assault']['shots'];
			$value2 = $player['assault']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['hits'];
			$value1 = $player['sniper']['shots'];
			$value2 = $player['sniper']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['hits'];
			$value1 = $player['pistol']['shots'];
			$value2 = $player['pistol']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['hits'];
			$value1 = $player['smg']['shots'];
			$value2 = $player['smg']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['hits'];
			$value1 = $player['heavy']['shots'];
			$value2 = $player['heavy']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			fwrite($fp, "</tr>\n");
		}
	}
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 2)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Red\">$value</font><span>$value1</span></a></b></td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['assault']['hits'];
			$value1 = $player['assault']['shots'];
			$value2 = $player['assault']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['sniper']['hits'];
			$value1 = $player['sniper']['shots'];
			$value2 = $player['sniper']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['pistol']['hits'];
			$value1 = $player['pistol']['shots'];
			$value2 = $player['pistol']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['smg']['hits'];
			$value1 = $player['smg']['shots'];
			$value2 = $player['smg']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['kills'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['hits'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['headshots'];
			$stringData = "<td>$value</td>";
			fwrite($fp, $stringData);
			$value = $player['heavy']['hits'];
			$value1 = $player['heavy']['shots'];
			$value2 = $player['heavy']['accuracy'];
			$stringData = "<td><a class=\"tooltip\">$value2<span>$value/$value1</span></a></td>";
			fwrite($fp, $stringData);
			fwrite($fp, "</tr>\n");
		}
	}
	fwrite($fp, "</tbody></table>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<h2>Tickrates for Movie Makers</h2>\n");
	fwrite($fp, "This table has <em>approximate</em> tickrates which might serve for good movie clips. Includes things like 3-kills, 4-kills, aces and clutch rounds.<br>\n");
	fwrite($fp, "<a onclick =\"javascript:ShowHide(\"HiddenDiv3\")\" href=\"javascript:;\">Show/Hide</a>\n");
	fwrite($fp, "<div class=\"mid\" id=\"HiddenDiv3\" style=\"DISPLAY: block\" >\n");
	fwrite($fp, "<table class=\"sortable\">\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr class=\"heading\">\n");	
	fwrite($fp, "<th>Name</th>\n");
	fwrite($fp, "<th>Approximate Tickrate</th>\n");
	fwrite($fp, "<th>Details</th>\n");
	fwrite($fp, "</tr>\n");
	foreach ($movieWorthy as &$sub){
		fwrite($fp, "<tr>\n");
		$value = $sub['movieWorthyName'];
		$stringData = "<td>$value</td>";
		fwrite($fp, $stringData);
		$value = $sub['movieWorthyTick'];
		$stringData = "<td>$value</td>";
		fwrite($fp, $stringData);
		$value = $sub['movieWorthyDetails'];
		$stringData = "<td>$value</td>"; 
		fwrite($fp, $stringData);
		fwrite($fp, "</tr>\n");
	}	
	fwrite($fp, "</tbody>\n");
	fwrite($fp, "</table>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<h2>Player information:</h2>\n");
	fwrite($fp, "<a onclick =\"javascript:ShowHide(\"HiddenDiv4\")\" href=\"javascript:;\">Show/Hide</a>\n");
	fwrite($fp, "<div class=\"mid\" id=\"HiddenDiv4\" style=\"DISPLAY: block\" >\n");
	fwrite($fp, "<table class=\"sortable\">\n");
	fwrite($fp, "<tbody>\n");
	fwrite($fp, "<tr class=\"heading\">\n");
	fwrite($fp, "<th>Player</th> <th>STEAM-ID</th> <th>Profile link</th>\n");
	fwrite($fp, "</tr>\n");
	fwrite($fp, "<tr>\n");
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 3)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Blue\">$value</font><span>$value1</span></a></b></td><td>$value1</td><td><a href=\"$steamIdLink\" target=\"_blank\">Link</a></td></tr>\n";
			fwrite($fp, $stringData);
		}
	}
	foreach ($playersArray as &$player){
		if (($player['FHTeam'] != 0) && ($player['SHTeam'] != 0) && ($player['FHTeam']== 2)){
			fwrite($fp, "<tr>\n");
			$steamIdLink = "http://steamcommunity.com/profiles/";
			$steamIdLink = $steamIdLink . SteamIDStringToSteamID($player['steamid']);
			$value = $player['name'];
			$value1 = $player['steamid'];
			$stringData = "<td><b><a href=\"$steamIdLink\" style=\"text-decoration: none;\" target=\"_blank\" class=\"tooltip\"><font class=\"Red\">$value</font><span>$value1</span></a></b></td><td>$value1</td><td><a href=\"$steamIdLink\" target=\"_blank\">Link</a></td></tr>\n";
			fwrite($fp, $stringData);
		}
	}
	fwrite($fp, "</tbody></table>\n");
	fwrite($fp, "</div>\n");

	fwrite($fp, "\n");
	fwrite($fp, "<br>\n");
	fwrite($fp, "<h4><a href=\"#\" title=\"Click to return to the top\">Return to the top</a></h4>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "\n");
	fwrite($fp, "<div id=\"footer\">\n");
	fwrite($fp, "<div id=\"footer_container\" class=\"clearfix\">\n");
	fwrite($fp, "<div class=\"foot_col\">\n");
	fwrite($fp, "<h3>Copyright</h3>\n");
	fwrite($fp, "<p>COPYRIGHT 2011-2014 BFG-ESPORTS.COM SITE DESIGN BY NANO FROM ATF2L.ORG</p>\n");
	fwrite($fp, "</br>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "</div>\n");
	fwrite($fp, "\n");
	fwrite($fp, "</body>\n");
	fwrite($fp, "</html>\n");
	fclose($fp);
}
?>