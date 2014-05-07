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