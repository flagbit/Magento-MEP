<?php

class   Flagbit_MEP_Model_GoogleMapping_Import extends Mage_Core_Model_Abstract
{
    public function runImportWithFile($file)
    {

    }

    public function runImportWithUrl($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curl);
        curl_close($curl);
        if (!$content)
        {
            throw new Exception(curl_error($curl));
        }
        $this->runImportWithContent($content);
    }

    public function runImportWithUrls($urls)
    {
        $contents = [];
        foreach($urls as $locale => $url) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if(404 == $httpcode) {
                continue;
            }
            $contents[$locale] = $content;
            if (!$content)
            {
                throw new Exception(curl_error($curl));
            }
        }

        $this->runImportWithContentAndLocale($contents);
    }

    public function runImportWithContentAndLocale($contents) {
        function getId() {
            static $id = 1;
            return $id++;
        }

        $result = [];
        $list = [];

        foreach ($contents as $locale => $content) {
            $lines = explode("\n", $content);
            // Skip first line
            array_shift($lines);
            $result[$locale] = [
                'numId' => 0,
                'childs' => []
            ];
            foreach($lines as $line) {
                $line = trim($line);
                $entries = explode('>', $line);
                $entries = array_map(function ($v) {return trim($v);}, $entries);
                $parent = &$result[$locale];
                foreach($entries as $e) {
                    if(!isset($parent['childs'])) $parent['childs'] = [];

                    $id = $this->_getSlug($e) . '_' . $locale;

                    if(!isset($parent['childs'][$id])) {
                        $list[$id] = [
                            'id' => $id,
                            'numId' => getId(),
                            'name' => iconv(mb_detect_encoding($e), 'UTF-8', $e),
                            'parent' => &$parent,
                            'childs' => [],
                            'locale' => $locale
                        ];
                        $parent['childs'][$id] = $list[$id];
                    }
                    $parent = &$parent['childs'][$id];
                }
            }
        }

        function saveLines($values) {
            $query = 'INSERT INTO mep_google_taxonomies (taxonomy_id, parent_id, name, slug, locale) VALUES '
                . implode(',', $values) . ';';
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $writeConnection->query($query);
        }

        $counter = 1;
        $values = [];
        foreach($list as $e) {
            $values[] = '("' . $e['numId'] . '",'
                .'"' . $e['parent']['numId'] . '",'
                .'"' . $e['name'] . '",'
                .'"' . $e['id'] . '",'
                .'"' . $e['locale'] . '")';
            if(($counter % 20000) == 0) {
                saveLines($values);
                $values = [];
            }
            $counter++;
        }

        if(count($values) > 0) {
            saveLines($values);
        }
    }

    public function runImportWithContent($content)
    {
        $file = explode("\n", $content);
        $taxonomies = array();
        foreach ($file as $line)
        {
            $reset = true;
            if ($line[0] == '#' || strlen($line) == 0)
            {
                continue;
            }
            $lineToArray = explode('>', $line);
            foreach ($lineToArray as $taxonomy)
            {
                $key = $this->_getSlug($taxonomy);
                $node = array(
                    'name' => trim($taxonomy),
                    'children' => array(),
                    'mysql_id' => null,
                    'slug' => $key,
                    'parent_id' => 0
                );
                $taxonomyModel = Mage::getModel('mep/googleTaxonomies');
                $taxonomyModel->getResource()->load($taxonomyModel, $node['slug'], 'slug');
                Mage::log($node, null, 'google_import_debug.log');
                if ($reset)
                {
                    if (!array_key_exists($key, $taxonomies)) {
                        $taxonomyModel->setData('parent_id', $node['parent_id']);
                        $taxonomyModel->setData('name', $node['name']);
                        $taxonomyModel->setData('slug', $node['slug']);
                        $taxonomyModel->save();
                        $node['mysql_id'] = $taxonomyModel->getTaxonomyId();
                        $taxonomies[$key] = $node;
                    }
                    $previous = &$taxonomies[$key];
                    $reset = false;
                }
                else
                {
                    $node['parent_id'] = $previous['mysql_id'];
                    if (!array_key_exists($key, $previous['children'])) {
                        $taxonomyModel->setData('parent_id', $node['parent_id']);
                        $taxonomyModel->setData('name', $node['name']);
                        $taxonomyModel->setData('slug', $node['slug']);
                        $taxonomyModel->save();
                        $node['mysql_id'] = $taxonomyModel->getTaxonomyId();
                        $previous['children'][$key] = $node;
                    }
                    $previous = &$previous['children'][$key];
                }
            }
        }
    }

    protected function  _getSlug($taxonomy)
    {
        $taxonomy = iconv('UTF-8','ASCII//TRANSLIT',$taxonomy);
        $taxonomy = preg_replace('`[^A-Za-z0-9 ]`', '', $taxonomy);
        $taxonomy = str_replace(' ', '_', trim($taxonomy));
        return strtolower($taxonomy);
    }
}
