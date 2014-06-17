<?php

include_once('abstract.php');

class   Flagbit_MEP_UpdateGoogleCategories extends Mage_Shell_Abstract
{
    public function run()
    {
        $file = explode("\n", file_get_contents('taxonomy.de-DE.txt'));
        $taxonomies = array();
        $i = 0;
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
                if ($reset)
                {
                    if (!array_key_exists($key, $taxonomies)) {
                        $taxonomyModel = Mage::getModel('mep/googleTaxonomies');
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
                        $taxonomyModel = Mage::getModel('mep/googleTaxonomies');
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

$updateGoogleCategories = new Flagbit_MEP_UpdateGoogleCategories();
$updateGoogleCategories->run();