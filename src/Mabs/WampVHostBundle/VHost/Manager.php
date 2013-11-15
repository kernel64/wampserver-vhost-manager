<?php
namespace Mabs\WampVHostBundle\VHost;

use Mabs\WampVHostBundle\Exception\MabsWampVHostException;

class Manager
{

    protected $container;

    protected $options;

    public function __construct($container, $options = array())
    {
        $this->container = $container;
        $this->options = $options;
    }

    public function restartServers()
    {
        $shell = $this->getParameter('batch_file');
        exec($shell);
    }

    public function createNewVHost($serverName = null, $docDir = null)
    {
        if (is_null($serverName)) {
            throw new MabsWampVHostException(sprintf('ServerName "%s" must not be null.', $serverName));
        }
        if (is_null($docDir)) {
            throw new MabsWampVHostException(sprintf('DocumentRoot "%s" must not be null.', $docDir));
        }
        $vhostsDir = $this->getParameter('vhost_dir');
        if (is_dir($vhostsDir)) {
            $vHostConfig = $this->getParameter('definition');
            $vHostConfig = str_replace("#servername#", $serverName, $vHostConfig);
            $vHostConfig = str_replace("#documentroot#", $docDir, $vHostConfig);
            
            return $this->saveConfig($serverName, $vHostConfig);
        }
    }

    public function deleteVHost($fileName)
    {
        if (is_null($fileName)) {
            throw new MabsWampVHostException(sprintf('Filename "%s" must not be null.', $fileName));
        }
        
        $vhostsDir = $this->getParameter('vhost_dir');
        $fileName = trim($fileName);
        $file = $vhostsDir . $fileName;
    
        if (is_file($file) && (substr($file, -5) == ".conf")) {
            return unlink($file);
        }
        
        throw new MabsWampVHostException(sprintf('File "%s" not found.', $file));
    }
    
    
    public function updateVHost($fileName, $config)
    {
        if (is_null($fileName)) {
            throw new MabsWampVHostException(sprintf('Filename "%s" must not be null.', $fileName));
        }
        if (is_null($config)) {
            throw new MabsWampVHostException(sprintf('Your config "%s" must not be null.', $config));
        }
        
        return $this->saveConfig($fileName, $config);
    }

    public function showConfig($fileName)
    {
        if (is_null($fileName)) {
            throw new MabsWampVHostException(sprintf('Filename "%s" must not be null.', $fileName));
        }
        $vhostsDir = $this->getParameter('vhost_dir');
        if (is_dir($vhostsDir)) {
            $filename = $vhostsDir . $fileName;
            if (is_file($filename)) {
                return file_get_contents($filename);
            }
        }
    }

    public function getList()
    {
        $vhostsDir = $this->getParameter('vhost_dir');
        if (is_dir($vhostsDir)) {
            $handle = opendir($vhostsDir);
            $vhosts = array();
            while ($file = readdir($handle)) {
                if (is_file($vhostsDir . $file) && strstr($file, '.conf')) {
                    $vhosts[] = $this->getRootDir($vhostsDir . $file);
                }
            }
            closedir($handle);
            return $vhosts;
        }
        throw new MabsWampVHostException(sprintf('"%s" is not Dir.', $vhostsDir));
    }

    public function getRootDir($file)
    {
        $params = array();
        $fh = fopen($file, 'r');
        if ($fh === false) {
            throw new MabsWampVHostException(sprintf('Unable to read "%s"', $file));
        }
        
        while (! feof($fh)) {
            $line = fgets($fh);
            
            if (preg_match("/<VirtualHost/i", $line)) {
                preg_match("/<VirtualHost\s+(.+):(.+)\s*>/i", $line, $results);
                if (isset($results[1])) {
                    $hostname = $results[1];
                }
                if (isset($results[2])) {
                    $port = $results[2];
                }
                $rule = true;
            }
            
            if ($rule) {
                if (preg_match("/ServerName/i", $line)) {
                    preg_match("/ServerName\s+(.+)\s*/i", $line, $results);
                    if (isset($results[1])) {
                        $hostname = $results[1];
                    }
                }
                
                if (preg_match("/DocumentRoot/i", $line)) {
                    preg_match("/DocumentRoot\s+(.+)\s*/i", $line, $results);
                    if (isset($results[1])) {
                        $docRoot = $results[1];
                    }
                }
            }
            
            if (preg_match("/<\/VirtualHost>/i", $line)) {
                $params['name']     = $hostname;
                $params['basename'] = basename($file);
                $params['port']     = ($port == '80') ? '80' : $port;
                $params['docRoot']  = $docRoot;
                $rule = false;
            }
        }
        fclose($fh);
        
        return $params;
    }

    protected function getParameter($name)
    {
        if (isset($this->options[$name]))
            return $this->options[$name];
        throw new MabsWampVHostException(sprintf('Parameter "%s" not found.', $name));
    }

    protected function saveConfig($servername, $config)
    {
        $vhostsDir = $this->getParameter('vhost_dir');
        $servername = trim($servername);
        $file = $vhostsDir . $servername;
        if (substr($file, -5) != ".conf") {
            $file .=".conf";
        }

        return file_put_contents($file, $config);
    }
}