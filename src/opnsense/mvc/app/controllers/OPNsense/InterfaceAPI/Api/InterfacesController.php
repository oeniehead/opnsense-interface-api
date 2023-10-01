<?php

namespace OPNsense\InterfaceAPI\Api;

use \OPNsense\Base\ApiControllerBase;
use \OPNsense\Core\Config;
use \OPNsense\Core\Backend;

use DOMDocument;

function toAssocArray($element) {
    $result = array();

    if(get_class($element) === "DOMNodeList") {
        foreach($element as $child) {
            [$name, $value] = toAssocArray($child);

            if($name != "#text") $result[$name] = $value;
        }

        return $result;
    }
    else {
        $name = $element->nodeName;

        if($element->childNodes->length > 1) {
            $result = toAssocArray($element->childNodes);
        }
        else {
            $result = $element->textContent;
        }

        return [$name, $result];
    }
}


class InterfacesController extends ApiControllerBase
{
    public function getAction() {
        $configObj = Config::getInstance();

        $definitions = toAssocArray($configObj->xpath("//interfaces/*"));
        $configs = json_decode((new Backend())->configdRun("interface list ifconfig"), true);

        // print_r($definitions);
        // print_r($configs);

        $output = array();

        foreach($definitions as $internal_name => $definition) {
            $name = empty($definition["descr"]) ? $internal_name: $definition["descr"];
            $ifname = $definition["if"];
            $config = $configs[$ifname];

            $output[] = array(
                "name" => $name,
                "ifname" => $ifname,
                "macaddr" => $config["macaddr"],
                "ipv4" => $config["ipv4"],
                "ipv6" => $config["ipv6"],
            );
        }

        //print_r($output);

        return $output;
    }
}