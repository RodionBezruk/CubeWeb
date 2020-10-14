<?php
class OS_Guess
{
    var $sysname;
    var $nodename;
    var $cpu;
    var $release;
    var $extra;
    function OS_Guess($uname = null)
    {
        list($this->sysname,
             $this->release,
             $this->cpu,
             $this->extra,
             $this->nodename) = $this->parseSignature($uname);
    }
    function parseSignature($uname = null)
    {
        static $sysmap = array(
            'HP-UX' => 'hpux',
            'IRIX64' => 'irix',
        );
        static $cpumap = array(
            'i586' => 'i386',
            'i686' => 'i386',
            'ppc' => 'powerpc',
        );
        if ($uname === null) {
            $uname = php_uname();
        }
        $parts = split('[[:space:]]+', trim($uname));
        $n = count($parts);
        $release = $machine = $cpu = '';
        $sysname = $parts[0];
        $nodename = $parts[1];
        $cpu = $parts[$n-1];
        $extra = '';
        if ($cpu == 'unknown') {
            $cpu = $parts[$n-2];
        }
        switch ($sysname) {
            case 'AIX' :
                $release = "$parts[3].$parts[2]";
                break;
            case 'Windows' :
                switch ($parts[1]) {
                    case '95/98':
                        $release = '9x';
                        break;
                    default:
                        $release = $parts[1];
                        break;
                }
                $cpu = 'i386';
                break;
            case 'Linux' :
                $extra = $this->_detectGlibcVersion();
                $release = ereg_replace('^([[:digit:]]+\.[[:digit:]]+).*', '\1', $parts[2]);
                break;
            case 'Mac' :
                $sysname = 'darwin';
                $nodename = $parts[2];
                $release = $parts[3];
                if ($cpu == 'Macintosh') {
                    if ($parts[$n - 2] == 'Power') {
                        $cpu = 'powerpc';
                    }
                }
                break;
            case 'Darwin' :
                if ($cpu == 'Macintosh') {
                    if ($parts[$n - 2] == 'Power') {
                        $cpu = 'powerpc';
                    }
                }
                $release = ereg_replace('^([[:digit:]]+\.[[:digit:]]+).*', '\1', $parts[2]);
                break;
            default:
                $release = ereg_replace('-.*', '', $parts[2]);
                break;
        }
        if (isset($sysmap[$sysname])) {
            $sysname = $sysmap[$sysname];
        } else {
            $sysname = strtolower($sysname);
        }
        if (isset($cpumap[$cpu])) {
            $cpu = $cpumap[$cpu];
        }
        return array($sysname, $release, $cpu, $extra, $nodename);
    }
    function _detectGlibcVersion()
    {
        static $glibc = false;
        if ($glibc !== false) {
            return $glibc; 
        }
        $major = $minor = 0;
        include_once "System.php";
        if (@file_exists('/usr/include/features.h') &&
              @is_readable('/usr/include/features.h')) {
            if (!@file_exists('/usr/bin/cpp') || !@is_executable('/usr/bin/cpp')) {
                $features_file = fopen('/usr/include/features.h', 'rb');
                while (!feof($features_file)) {
                    $line = fgets($features_file, 8192);
                    if (!$line || (strpos($line, '#define') === false)) {
                        continue;
                    }
                    if (strpos($line, '__GLIBC__')) {
                        $line = preg_split('/\s+/', $line);
                        $glibc_major = trim($line[2]);
                        if (isset($glibc_minor)) {
                            break;
                        }
                        continue;
                    }
                    if (strpos($line, '__GLIBC_MINOR__'))  {
                        $line = preg_split('/\s+/', $line);
                        $glibc_minor = trim($line[2]);
                        if (isset($glibc_major)) {
                            break;
                        }
                        continue;
                    }
                }
                fclose($features_file);
                if (!isset($glibc_major) || !isset($glibc_minor)) {
                    return $glibc = '';
                }
                return $glibc = 'glibc' . trim($glibc_major) . "." . trim($glibc_minor) ;
            } 
            $tmpfile = System::mktemp("glibctest");
            $fp = fopen($tmpfile, "w");
            fwrite($fp, "#include <features.h>\n__GLIBC__ __GLIBC_MINOR__\n");
            fclose($fp);
            $cpp = popen("/usr/bin/cpp $tmpfile", "r");
            while ($line = fgets($cpp, 1024)) {
                if ($line{0} == '#' || trim($line) == '') {
                    continue;
                }
                if (list($major, $minor) = explode(' ', trim($line))) {
                    break;
                }
            }
            pclose($cpp);
            unlink($tmpfile);
        } 
        if (!($major && $minor) && @is_link('/lib/libc.so.6')) {
            if (ereg('^libc-(.*)\.so$', basename(readlink('/lib/libc.so.6')), $matches)) {
                list($major, $minor) = explode('.', $matches[1]);
            }
        }
        if (!($major && $minor)) {
            return $glibc = '';
        }
        return $glibc = "glibc{$major}.{$minor}";
    }
    function getSignature()
    {
        if (empty($this->extra)) {
            return "{$this->sysname}-{$this->release}-{$this->cpu}";
        }
        return "{$this->sysname}-{$this->release}-{$this->cpu}-{$this->extra}";
    }
    function getSysname()
    {
        return $this->sysname;
    }
    function getNodename()
    {
        return $this->nodename;
    }
    function getCpu()
    {
        return $this->cpu;
    }
    function getRelease()
    {
        return $this->release;
    }
    function getExtra()
    {
        return $this->extra;
    }
    function matchSignature($match)
    {
        if (is_array($match)) {
            $fragments = $match;
        } else {
            $fragments = explode('-', $match);
        }
        $n = count($fragments);
        $matches = 0;
        if ($n > 0) {
            $matches += $this->_matchFragment($fragments[0], $this->sysname);
        }
        if ($n > 1) {
            $matches += $this->_matchFragment($fragments[1], $this->release);
        }
        if ($n > 2) {
            $matches += $this->_matchFragment($fragments[2], $this->cpu);
        }
        if ($n > 3) {
            $matches += $this->_matchFragment($fragments[3], $this->extra);
        }
        return ($matches == $n);
    }
    function _matchFragment($fragment, $value)
    {
        if (strcspn($fragment, '*?') < strlen($fragment)) {
            $reg = '^' . str_replace(array('*', '?', '/'), array('.*', '.', '\\/'), $fragment) . '$';
            return eregi($reg, $value);
        }
        return ($fragment == '*' || !strcasecmp($fragment, $value));
    }
}
?>
