<?php

    

    function flatten() {
        $json = '{"vfs":[{"id":"rootfolder","label":"\/","folder":true,"children":[{"label":"folder1","id":"folder","children":[{"label":"folder2","id":"folder","children":[{"id":"1ce87c0865e5462af6cf962b6aa2e28d","label":"params_37.xml","filename":"\/var\/www\/data\/admin\/files\/folder1\/folder2\/params_37.xml"}]},{"id":"8a8aecaab4c2ecd097f84e518366fae0","label":"params_13.xml","filename":"\/var\/www\/data\/admin\/files\/folder1\/params_13.xml"}]}]}]}';
        $data = json_decode($json, true);
        // var_dump($data['vfs'][0]['children']);
        $vfs = &$data['vfs'][0]['children'];
        $flat = array();
        $ref = &$flat;
        // var_dump($vfs);
        flat_r($vfs, $ref);
        return $flat;
    }

    function flat_r(&$vfs, &$flat) {
        foreach ($vfs as $entry) {
            if (array_key_exists('filename', $entry)) {
                $flat_entry = array('id' => $entry['id'], 'label' => $entry['label'], 'filename' => $entry['filename']);
                array_push($flat, $flat_entry);
            } elseif (array_key_exists('children', $entry)) {
                flat_r($entry['children'], $flat);
            }
        }
    }


    $test = flatten();
    var_dump($test);

?>