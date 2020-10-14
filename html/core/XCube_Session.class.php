<?php
class XCube_Session
{
    var $mSessionName = '';
    var $mSessionLifetime = 0;
    var $mSetupSessionHandler = null;
    var $mGetSessionCookiePath = null;
    function XCube_Session($sessionName='', $sessionExpire=0)
    {
        $this->setParam($sessionName, $sessionExpire);
        $this->mSetupSessionHandler =& new XCube_Delegate();
        $this->mSetupSessionHandler->register('XCube_Session.SetupSessionHandler');
        $this->mGetSessionCookiePath =& new XCube_Delegate();
        $this->mGetSessionCookiePath->register('XCube_Session.GetSessionCookiePath');
    }
    function setParam($sessionName='', $sessionExpire=0)
    {
        $allIniArray = ini_get_all();
        if ($sessionName !='') {
            $this->mSessionName = $sessionName;
        } else {
            $this->mSessionName = $allIniArray['session.name']['global_value'];
        }
        if (!empty($sessionExpire)) {
            $this->mSessionLifetime = 60 * $sessionExpire;
        } else {
            $this->mSessionLifetime = $allIniArray['session.cookie_lifetime']['global_value'];
        }
    }
    function start()
    {
        $this->mSetupSessionHandler->call();
        session_name($this->mSessionName);
        session_set_cookie_params($this->mSessionLifetime, $this->_cookiePath());
        session_start();
        if (!empty($this->mSessionLifetime) && isset($_COOKIE[$this->mSessionName])) {
            setcookie($this->mSessionName, session_id(), time() + $this->mSessionLifetime, $this->_cookiePath());
        }
    }
    function destroy($forceCookieClear = false)
    {
        $currentSessionName = session_name();
        if (isset($_COOKIE[$currentSessionName])) {
            if ($forceCookieClear || ($currentSessionName != $this->mSessionName)) {
                setcookie($currentSessionName, '', time() - 86400, $this->_cookiePath());
            }
        }
        session_destroy();
    }
    function regenerate()
    {
        $oldSessionID = session_id();
        session_regenerate_id();
        $newSessionID = session_id();
        session_id($oldSessionID);
        $this->destroy();
        $oldSession = $_SESSION;
        session_id($newSessionID);
        $this->start();
        $_SESSION = array();
        foreach (array_keys($oldSession) as $key) {
            $_SESSION[$key] = $oldSession[$key];
        }
    }
    function rename()
    {
        if (session_name() != $this->mSessionName) {
            $oldSessionID = session_id();
            $oldSession = $_SESSION;
            $this->destroy();
            session_id($oldSessionID);
            $this->start();
            $_SESSION = array();
            foreach (array_keys($oldSession) as $key) {
                $_SESSION[$key] = $oldSession[$key];
            }
        }
    }
    function _cookiePath()
    {
        static $sessionCookiePath = null;
        if (empty($sessionCookiePath)) {
            $this->mGetSessionCookiePath->call(new XCube_Ref($sessionCookiePath));
            if (empty($sessionCookiePath)) {
                $sessionCookiePath = '/';
            }
        }
        return $sessionCookiePath;
    }
}
?>
