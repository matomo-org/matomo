<?php

class TrackerDbQueryGenerator
{

    private $returnUserIds = [];
    private $actionsUrl = [];
    private $campaigns = [];
    private $referers = [];

    CONST RETURN_VISITOR_POOL_SIZE = 10000;
    CONST ACTIONS_POOL_SIZE = 1000000;

    public function __construct() {

        $keywords = ['books','reading','pages','story','fiction','author'];
        $medium = ['ppc','website','email'];
        $source = ['bing','google','doubleclick'];
        for ($i = 0; $i < 50;$i++) {

            $id = bin2hex(random_bytes(3));
            $campaign = [
                'id' => $id,
                'content' => '',
                'group' => '',
                'keyword' => $keywords[array_rand($keywords)],
                'medium' => $medium[array_rand($medium)],
                'name' => 'campaign_'.$id,
                'placement' => '',
                'source' => $source[array_rand($source)]
            ];

            $this->campaigns[] = $campaign;
        }

        for ($i = 0; $i < 500;$i++) {

            $id = bin2hex(random_bytes(4));
            $referer = [
                'name' => 'ref_'.$id,
                'url' => 'https://domain'.$id.'.com',
                'type' => array_rand([1,2,3]),
                'keyword' => $keywords[array_rand($keywords)],
            ];

            $this->referers[] = $referer;
        }

    }


    public function getRandomActionURL(): string
    {

        // Choose an existing actionsUrl 50% of the time if there are at least 100 in the pool
        // or 100% of the time if the pool is full
        if (count($this->actionsUrl) > 100 && (rand(0, 100) < 50 || count($this->actionsUrl) >= self::ACTIONS_POOL_SIZE)) {
           return $this->actionsUrl[array_rand($this->actionsUrl)];
        }

        $newActionUrl = "/page_".bin2hex(random_bytes(10));
        $this->actionsUrl[] = $newActionUrl;

        return $newActionUrl;
    }

    /**
     * Get a new visitor id, with a [$chanceReturning] possibility of being a previous visitor
     *
     * @param int $chanceReturning
     *
     * @return string
     * @throws Exception
     */
    public function getVisitor(int $chanceReturning) : string
    {
        if (rand(0,100) <= $chanceReturning && count($this->returnUserIds) > 0) {
            return $this->returnUserIds[array_rand($this->returnUserIds)];
        }

        $idvisitor = random_bytes(8);

        // 1% chance to update a return user id if the pool is full
        if (count($this->returnUserIds) >= self::RETURN_VISITOR_POOL_SIZE && rand(0,100) === 1) {

            // Never replace the early return visitors, we want some selects at the quiet end of the index
            $this->returnUserIds[rand(self::RETURN_VISITOR_POOL_SIZE/10,self::RETURN_VISITOR_POOL_SIZE)] = $idvisitor;
        }

        // If ppol is not full then add this id
        if (count($this->returnUserIds)< self::RETURN_VISITOR_POOL_SIZE) {
            $this->returnUserIds[] = $idvisitor;
        }

        return $idvisitor;
    }

    public function getCheckActionExistsQuery(string $actionUrl): array
    {

        $sql = "SELECT `idaction`, `type`, `name` 
                FROM log_action 
                WHERE ( hash = CRC32(:name) AND name = :name AND type = 1) OR ( hash = CRC32(:name) AND name = :name AND type = 1)";

        $bind = [
            ':name' => $actionUrl
        ];
        return ['sql' => $sql, 'bind' => $bind];
    }

    public function getInsertActionQuery(string $actionUrl): array
    {
        $sql = "INSERT INTO log_action (name, hash, type, url_prefix) VALUES (:name, CRC32(:name), 1, null);";
        $bind = [
            ':name' => $actionUrl
        ];
        return ['sql' => $sql, 'bind' => $bind];
    }


    public function getCheckIfNewVisitorQuery(string $idvisitor, int $site): array
    {

        $timeLookback = date('Y-m-d H:i:s', time() - 1800);

        $sql = "SELECT visit_last_action_time, visit_first_action_time, idvisitor, idvisit, user_id, visit_exit_idaction_url, visit_exit_idaction_name,
         visitor_returning, visitor_seconds_since_first, visitor_seconds_since_order, visitor_count_visits, visit_goal_buyer, location_country,
         location_region, location_city, location_latitude, location_longitude, referer_name, referer_keyword, referer_type, idsite, profilable,
         visit_entry_idaction_url, visit_total_actions, visit_total_interactions, visit_total_searches, referer_url, config_browser_name,
         config_client_type, config_device_brand, config_device_model, config_device_type, visit_total_events, visit_total_time, location_ip,
         location_browser_lang, last_idlink_va, custom_dimension_1, custom_dimension_2, custom_dimension_3, custom_dimension_4, custom_dimension_5
         FROM log_visit 
         WHERE idsite = :site AND visit_last_action_time >= :lastaction AND idvisitor = UNHEX(:idvisitor) ORDER BY visit_last_action_time DESC LIMIT 1";

        // Removed FORCE INDEX (index_idsite_idvisitor)

        $bind = [':lastaction' => $timeLookback, ':idvisitor' => bin2hex($idvisitor), ':site' => $site];
        return ['sql' => $sql, 'bind' => $bind];
    }

    public function getRandomIP() : string
    {
        $ipString = rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
        $ip = @inet_pton($ipString);
        return $ip === false ? "\x00\x00\x00\x00" : $ip;
    }

    public function getRandomLang() : string
    {
        $langs = ['ab','aa','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','bm','ba','eu','be','bn','bh','bi','bs','br','bg','my','ca','km','ch','ce','ny','zh','cu','cv','kw','co','cr','hr','cs','da','dv','nl','dz','en','eo','et','ee','fo','fj','fi','fr','ff','gd','gl','lg','ka','de','ki','el','kl','gn','gu','ht','ha','he','hz','hi','ho','hu','is','io','ig','id','ia','ie','iu','ik','ga','it','ja','jv','kn','kr','ks','kk','rw','kv','kg','ko','kj','ku','ky','lo','la','lv','lb','li','ln','lt','lu','mk','mg','ms','ml','mt','gv','mi','mr','mh','ro','mn','na','nv','nd','ng','ne','se','no','nb','nn','ii','oc','oj','or','om','os','pi','pa','ps','fa','pl','pt','qu','rm','rn','ru','sm','sg','sa','sc','sr','sn','sd','si','sk','sl','so','st','nr','es','su','sw','ss','sv','tl','ty','tg','ta','tt','te','th','bo','ti','to','ts','tn','tr','tk','tw','ug','uk','ur','uz','ve','vi','vo','wa','cy','fy','wo','xh','yi','yo','za','zu'];
        return $langs[array_rand($langs)];
    }

    public function getRandomResolution() : string
    {
        $res = ['1024x768', '1600x1200', '2048x1536','1280x720','1366x768','1600x900','1920x1080','2560x1440','1280x800','1440x900','1680x1050','1920x1200','2560x1600'];
        return $res[array_rand($res)];
    }

    public function getRandomCountryA2() : string
    {
        $a2s = ['AD','AE','AF','AG','AI','AL','AM','AO','AQ','AR','AS','AT','AU','AW','AX','AZ','BA','BB','BD','BE','BF','BG','BH','BI','BJ','BL','BM','BN','BO','BQ','BR','BS','BT','BV','BW','BY','BZ','CA','CC','CD','CF','CG','CH','CI','CK','CL','CM','CN','CO','CR','CU','CV','CW','CX','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET','FI','FJ','FK','FM','FO','FR','GA','GB','GD','GE','GF','GG','GH','GI','GL','GM','GN','GP','GQ','GR','GS','GT','GU','GW','GY','HK','HM','HN','HR','HT','HU','ID','IE','IL','IM','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI','KM','KN','KP','KR','KW','KY','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD','ME','MF','MG','MH','MK','ML','MM','MN','MO','MP','MQ','MR','MS','MT','MU','MV','MW','MX','MY','MZ','NA','NC','NE','NF','NG','NI','NL','NO','NP','NR','NU','NZ','OM','PA','PE','PF','PG','PH','PK','PL','PM','PN','PR','PS','PT','PW','PY','QA','RE','RO','RS','RU','RW','SA','SB','SC','SD','SE','SG','SH','SI','SJ','SK','SL','SM','SN','SO','SR','SS','ST','SV','SX','SY','SZ','TC','TD','TF','TG','TH','TJ','TK','TL','TM','TN','TO','TR','TT','TV','TW','TZ','UA','UG','UM','US','UY','UZ','VA','VC','VE','VG','VI','VN','VU','WF','WS','YE','YT','ZA','ZM','ZW'];
        return strtolower($a2s[array_rand($a2s)]);
    }

    public function getInsertVisitorQuery(string $idvisitor, string $entryActionUrlId, int $timestamp, int $site): array
    {

        $sql = "
        INSERT INTO log_visit (idvisitor, config_id, location_ip, idsite, profilable, visit_first_action_time, 
                                      visit_goal_buyer, visit_goal_converted, visit_last_action_time, visitor_returning,
                                      visitor_seconds_since_first, visitor_seconds_since_order, visitor_count_visits, 
                                      visit_entry_idaction_name, visit_entry_idaction_url, visit_exit_idaction_name, 
                                      visit_exit_idaction_url, visit_total_actions, visit_total_interactions, visit_total_searches,
                                      referer_keyword, referer_name, referer_type, referer_url, location_browser_lang, config_browser_engine,
                                      config_browser_name, config_browser_version, config_client_type, config_device_brand, config_device_model,
                                      config_device_type, config_os, config_os_version, visit_total_events, visitor_localtime, 
                                      visitor_seconds_since_last, config_resolution, config_cookie, config_flash, config_java, 
                                      config_pdf, config_quicktime, config_realplayer, config_silverlight, config_windowsmedia, 
                                      visit_total_time, location_country) 
        VALUES (:idvisitor, :config_id, :location_ip, :idsite, :profilable, :visit_first_action_time, 
                                      :visit_goal_buyer, :visit_goal_converted, :visit_last_action_time, :visitor_returning,
                                      :visitor_seconds_since_first, :visitor_seconds_since_order, :visitor_count_visits, 
                                      :visit_entry_idaction_name, :visit_entry_idaction_url, :visit_exit_idaction_name, 
                                      :visit_exit_idaction_url, :visit_total_actions, :visit_total_interactions, :visit_total_searches,
                                      :referer_keyword, :referer_name, :referer_type, :referer_url, :location_browser_lang, :config_browser_engine,
                                      :config_browser_name, :config_browser_version, :config_client_type, :config_device_brand, :config_device_model,
                                      :config_device_type, :config_os, :config_os_version, :visit_total_events, :visitor_localtime, 
                                      :visitor_seconds_since_last, :config_resolution, :config_cookie, :config_flash, :config_java, 
                                      :config_pdf, :config_quicktime, :config_realplayer, :config_silverlight, :config_windowsmedia, 
                                      :visit_total_time, :location_country)
        ";

        $campaign = null;
        if (rand(0,100) < 5) {
            $campaign = $this->campaigns[array_rand($this->campaigns)];
        }

        $referer = null;
        if (rand(0,100) < 33) {
            $referer = $this->referers[array_rand($this->referers)];
        }

        $bind = [':idvisitor' => $idvisitor,
                 ':config_id' => random_bytes(8),
                 ':location_ip' => $this->getRandomIP(),
                 ':idsite' => $site,
                 ':profilable' => 1,
                 ':visit_first_action_time' => date('Y-m-d H:i:s', $timestamp),
                 ':visit_goal_buyer' => 0,
                 ':visit_goal_converted' => 0,
                 ':visit_last_action_time' => date('Y-m-d H:i:s', $timestamp),
                 ':visitor_returning' => 0,
                 ':visitor_seconds_since_first' => 0,
                 ':visitor_seconds_since_order' => null,
                 ':visitor_count_visits' => 1,
                 ':visit_entry_idaction_name' => null,
                 ':visit_entry_idaction_url' => $entryActionUrlId,
                 ':visit_exit_idaction_name' => null,
                 ':visit_exit_idaction_url' => null,
                 ':visit_total_actions' => 1,
                 ':visit_total_interactions' => 1,
                 ':visit_total_searches' => 0,
                 ':visit_total_time' => 0,
                 ':visit_total_events' => 0,
                 ':visitor_localtime' => rand(1,23).':'.rand(0,59).':'.rand(0,59),
                 ':visitor_seconds_since_last' => 0,

                 ':referer_keyword' => ($referer ? $referer['keyword'] : null),
                 ':referer_name' => ($referer ? $referer['name'] : null),
                 ':referer_type' => ($referer ? $referer['type'] : null),
                 ':referer_url' => ($referer ? $referer['url'] : null),

                 ':location_country' => $this->getRandomCountryA2(),
                 ':location_browser_lang' => $this->getRandomLang(),
                 ':config_browser_engine' => '',
                 ':config_browser_name' => '',
                 ':config_browser_version' => '',
                 ':config_client_type' => 1,
                 ':config_device_brand' => '',
                 ':config_device_model' => '',
                 ':config_device_type' => rand(0,3),
                 ':config_os' => '',
                 ':config_os_version' => '',
                 ':config_resolution' => $this->getRandomResolution(),
                 ':config_cookie' => rand(0,1),
                 ':config_flash' => rand(0,1),
                 ':config_java' => rand(0,1),
                 ':config_pdf' => rand(0,1),
                 ':config_quicktime' => rand(0,1),
                 ':config_realplayer' => rand(0,1),
                 ':config_silverlight' => rand(0,1),
                 ':config_windowsmedia' => rand(0,1),

            ];

        return ['sql' => $sql, 'bind' => $bind];

    }

    public function getUpdateVisitQuery(int $idvisit, string $firstActionTime, int $timestamp, int $site)
    {
        $sql = "
         UPDATE log_visit
         SET profilable = 1, visit_last_action_time = :lastaction, visitor_seconds_since_order = 0, visit_exit_idaction_name = null, 
             visit_exit_idaction_url = null, visit_total_actions = visit_total_actions + 1, 
             visit_total_interactions = visit_total_interactions + 1 , visit_total_time = :visittotaltime
         WHERE idsite = :site AND idvisit = :idvisit
        ";

        $first = strtotime($firstActionTime);

        if ($first) {
            $totalTime = $timestamp - $first;
        } else {
            $totalTime = 0;
        }
        if ($totalTime < 0) {
            $totalTime = 1;
        }

        $bind = [':lastaction' => date('Y-m-d H:i:s', $timestamp), ':idvisit' => $idvisit, ':visittotaltime' => $totalTime, ':site' => $site];
        return ['sql' => $sql, 'bind' => $bind];
    }

    public function getInsertActionLinkQuery(string $idvisitor, int $idvisit, string $idaction, int $timestamp, int $site): array
    {
        $sql = "
        INSERT INTO log_link_visit_action (idvisit, idsite, idvisitor, idaction_url, idaction_url_ref, idaction_name_ref,
        server_time, idpageview, pageview_position, time_spent_ref_action, time_dom_processing, time_network, time_server, 
        time_transfer, idaction_name)
        VALUES (:idvisit, :idsite, :idvisitor, :idaction_url, :idaction_url_ref, :idaction_name_ref,
        :server_time, :idpageview, :pageview_position, :time_spent_ref_action, :time_dom_processing, :time_network,
        :time_server, :time_transfer, :idaction_name)
        ";

        $bind = [
            ':idvisit' => $idvisit,
            ':idsite' => $site,
            ':idvisitor' => $idvisitor,
            ':idaction_url' => $idaction,
            ':idaction_url_ref' => $idaction,
            ':idaction_name' => null,
            ':idaction_name_ref' => null,
            ':server_time' => date('Y-m-d H:i:s', $timestamp),
            ':idpageview' => bin2hex(random_bytes(3)),
            ':pageview_position' => rand(1,10),
            ':time_spent_ref_action' => rand(1,1000),
            ':time_dom_processing' => rand(1,1000),
            ':time_network' => rand(1,1000),
            ':time_server' => rand(1,1000),
            ':time_transfer' => rand(1,1000)
        ];

        return ['sql' => $sql, 'bind' => $bind];
    }

    public function getInsertConversionQuery(string $idvisitor, int $idvisit, string $idaction, string $url, int $timestamp,
                                             int $idlink_va, int $idgoal, int $site): array
    {

        $sql = "
        INSERT INTO log_conversion (idvisit, idsite, idvisitor, server_time, idaction_url, idlink_va, idgoal, buster,
            url, revenue, visitor_count_visits, visitor_returning, visitor_seconds_since_first, config_browser_name,
            config_client_type, config_device_brand, config_device_model, config_device_type)
        VALUES (:idvisit, :idsite, :idvisitor, :server_time, :idaction_url, :idlink_va, :idgoal, :buster,
            :url, :revenue, :visitor_count_visits, :visitor_returning, :visitor_seconds_since_first, :config_browser_name,
            :config_client_type, :config_device_brand, :config_device_model, :config_device_type)
        ";

        $bind = [
            ':idvisit' => $idvisit,
            ':idsite' => $site,
            ':idvisitor' => $idvisitor,
            ':server_time' => date('Y-m-d H:i:s', $timestamp),
            ':idaction_url' => $idaction,
            ':idlink_va' => $idlink_va,
            ':idgoal' => $idgoal,
            ':buster' => 0,
            ':url' => $url,
            ':revenue' => round(mt_rand() / mt_getrandmax(),2),
            ':visitor_count_visits' => rand(1,10),
            ':visitor_returning' => rand(0,1),
            ':visitor_seconds_since_first' => rand(1,5000),
            ':config_browser_name' => '',
            ':config_client_type' => 1,
            ':config_device_brand' => '',
            ':config_device_model' => '',
            ':config_device_type' => 0
        ];

        return ['sql' => $sql, 'bind' => $bind];
    }

}
