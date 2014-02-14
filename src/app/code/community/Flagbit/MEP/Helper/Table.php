<?php

class Flagbit_MEP_Helper_Table extends Mage_Core_Helper_Abstract {

    protected $_profile;

    protected $_delimiter;

    protected $_enclosure;

    public function toHtmlTable($csv, $profileId) {
        $this->_delimiter = $this->_getProfile($profileId)->getDelimiter();
        $this->_enclosure = $this->_getProfile($profileId)->getEnclose();
        $rows = explode("\n", $csv);
        $content = '<table class="sortable resizable" id="preview-table">';
        $content .= $this->_createHeader($rows[0]);
        unset($rows[0]);
        $content .= '<tbody>';
        foreach ($rows as $row) {
            $content .= $this->_createRow($row);
        }
        $content .= '</tbody></table>';
        return $content;
    }

    protected function  _createHeader($row) {
        $row = explode($this->_delimiter, $row);
        array_walk($row, array($this, '_cleanEnclosure'));
        $data = implode('</th><th>', $row);
        if (!empty($data)) {
            $content = '<thead><tr>';
            $content .= '<th>' . $data . '</th>';
            $content .= '</tr></thead>';
            return $content;
        }
        return '';
    }

    protected function  _createRow($row) {
        $row = explode($this->_delimiter, $row);
        array_walk($row, array($this, '_cleanEnclosure'));
        $data = implode('</td><td>', $row);
        if (!empty($data)) {
            $content = '<tr>';
            $content .= '<td>' . $data . '</td>';
            $content .= '</tr>';
            return $content;
        }
        return '';
    }

    protected function  _getProfile($profileId) {
        if (!$this->_profile) {
            $this->_profile = Mage::getModel('mep/profile')->load($profileId);
        }
        return $this->_profile;
    }

    protected function  _cleanEnclosure(&$data, $id) {
        $data = trim(rtrim($data, $this->_enclosure), $this->_enclosure);
    }
}