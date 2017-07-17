<?php

class McpControl {
    private $user = 'ops';
    private $pass = '9ijn(IJN';
    private $url = 'http://www.thedailyzen.net/mcp/mcp_ops.php';
    
    private function makeRequest($args = array()) {
        $args['u'] = $this->user;
        $args['p'] = $this->pass;
        // Always pass ops_uid if it's set.
        if (isset($GLOBALS['userid'])) {
            $args['ops_uid'] = $GLOBALS['userid'];
            if (isset($_COOKIE['pmd_suuser']) && $_COOKIE['pmd_suuser']) {
                $args['ops_uid'] .= "@".$_COOKIE['pmd_suuser'];
            }
        } else {
            $args['ops_uid'] = '0'; // System action by a non-logged in user.
        }
        $request = $this->url."?".http_build_query($args,'','&');
        // echo "<b>".htmlentities($request)."</b><br />";
        // Supress warnings from file_get_contents because if we don't.. it'll show the url requested (incl. pass)
        return json_decode(@file_get_contents($request), true);
    }
    
    public function updateControl($user_id, $mcp_id, $fields) {
        if (!is_numeric($user_id)) {
            throw new Exception("Invalid user_id!");
        }
        if (!is_numeric($mcp_id)) {
            throw new Exception("Invalid mcp_id!");
        }
        if (!is_array($fields)) {
            throw new Exception("Invalid fields!");
        }
        $control = $this->getControls($user_id, $mcp_id);
        if (!isset($control[0])) {
            throw new Exception("Uknown MCP Control");
        }
        $control = $control[0];
        $args = array();
        $args['mode'] = 'update';
        if (!is_null($user_id)) {
            $args['dealerid'] = $user_id;
        }
        $args['id'] = $mcp_id;
        $args['starttime'] = isset($fields['starttime'])?$fields['starttime']:$control['starttime'];
        $args['stoptime'] = isset($fields['stoptime'])?$fields['stoptime']:$control['stoptime'];
        $args['actionlist'] = isset($fields['actionlist'])?$fields['actionlist']:$control['actionlist'];
        $args['override'] = isset($fields['override'])?$fields['override']:$control['override'];
        $args['paused'] = isset($fields['paused'])?$fields['paused']:$control['paused'];
        $args['active'] = isset($fields['active'])?$fields['active']:$control['active'];
        $return = $this->makeRequest($args);
        if ($return['id'] == $args['id']
                && $return['starttime'] == $args['starttime']
                && $return['stoptime'] == $args['stoptime']
                && $return['actionlist'] == $args['actionlist']
                && $return['override'] == $args['override']
                && $return['paused'] == $args['paused']
                && $return['active'] == $args['active']) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getControls($user_id = null, $mcp_id = null) {
        if (!is_null($user_id) && !is_numeric($user_id)) {
            throw new Exception("Invalid user_id!");
        }
        if (!is_null($mcp_id)&&!is_numeric($mcp_id)) {
            throw new Exception("Invalid mcp_id!");
        }
        $args = array();
        if (!is_null($user_id)) {
            $args['dealerid'] = $user_id;
        }
        if (!is_null($mcp_id)) {
            $args['id'] = $mcp_id;
        }
        $controls = $this->makeRequest($args);
        foreach ($controls as $key => $control) {
            if (isset($control['active'])&&!$control['active']) {
                unset($controls[$key]);
            }
        }
        return $controls;
    }
    
    public function printAll() {
        echo "<pre>"; print_r($this->makeRequest()); echo "</pre>";
    }
}