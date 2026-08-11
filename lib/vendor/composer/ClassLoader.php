<?php

namespace Composer\Autoload;

class ClassLoader
{
    private $prefixes = array();
    private $fallbackDirs = array();
    private $useIncludePath = false;
    private $classMap = array();
    private $classMapAuthoritative = false;
    private $apcuPrefixes = array();
    private $apcuPrefixesWithFallback = array();
    private $prefixesPsr4 = array();
    private $fallbackDirsPsr4 = array();

    public function getPrefixes()
    {
        return $this->prefixes;
    }

    public function getFallbackDirs()
    {
        return $this->fallbackDirs;
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMap = $this->classMap + $classMap;
        } else {
            $this->classMap = $classMap;
        }
    }

    public function add($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirs = array_merge(
                    (array) $paths,
                    $this->fallbackDirs
                );
            } else {
                $this->fallbackDirs = array_merge(
                    $this->fallbackDirs,
                    (array) $paths
                );
            }

            return;
        }

        $first = $prefix[0];
        if (!isset($this->prefixes[$first][$prefix])) {
            $this->prefixes[$first][$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixes[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixes[$first][$prefix]
            );
        } else {
            $this->prefixes[$first][$prefix] = array_merge(
                $this->prefixes[$first][$prefix],
                (array) $paths
            );
        }
    }

    public function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr4
                );
            } else {
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    (array) $paths
                );
            }

            return;
        }

        $first = $prefix[0];
        if (!isset($this->prefixesPsr4[$first][$prefix])) {
            $this->prefixesPsr4[$first][$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixesPsr4[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixesPsr4[$first][$prefix]
            );
        } else {
            $this->prefixesPsr4[$first][$prefix] = array_merge(
                $this->prefixesPsr4[$first][$prefix],
                (array) $paths
            );
        }
    }

    public function set($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            $this->fallbackDirs = (array) $paths;
        } else {
            $this->prefixes[substr($prefix, 0, 1)][substr($prefix, 1)] = (array) $paths;
        }
    }

    public function setPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $this->prefixesPsr4[substr($prefix, 0, 1)][substr($prefix, 1)] = (array) $paths;
        }
    }

    public function setClassMapAuthoritative($classMapAuthoritative)
    {
        $this->classMapAuthoritative = $classMapAuthoritative;
    }

    public function isClassMapAuthoritative()
    {
        return $this->classMapAuthoritative;
    }

    public function setApcuPrefix($apcuPrefix, $fallback = true)
    {
        $this->apcuPrefix = function ($class) use ($apcuPrefix, $fallback) {
            $apcuLookup = $apcuPrefix . preg_replace('/[\\\\]/', '-', $class);
            $result = apcu_fetch($apcuLookup, $success);
            if (!$success && $fallback) {
                $result = false;
            }

            return $result;
        };
    }

    public function getApcuPrefixes()
    {
        return $this->apcuPrefixes;
    }

    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            includeFile($file);

            return true;
        }

        return null;
    }

    public function findFile($class)
    {
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }
        if ($this->classMapAuthoritative || isset($this->apcuPrefixes[$class])) {
            return $this->classMap[$class] = false;
        }

        $file = $this->findFileWithExtension($class, '.php');

        if ($file === null) {
            return $this->classMap[$class] = false;
        }

        return $file;
    }

    private function findFileWithExtension($class, $ext)
    {
        $logicalPathPsr4 = strtr($class, '\\', '/') . $ext;

        $first = $class[0];
        if (isset($this->prefixesPsr4[$first])) {
            foreach ($this->prefixesPsr4[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir . '/' . substr($logicalPathPsr4, strlen($prefix) + 1))) {
                            return $file;
                        }
                    }
                }
            }
        }

        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . '/' . $logicalPathPsr4)) {
                return $file;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr4)) {
            return $file;
        }

        return $this->classMap[$class] = false;
    }
}

function includeFile($file)
{
    include $file;
}
