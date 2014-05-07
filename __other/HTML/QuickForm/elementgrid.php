<?php
/**
 * This is a new element type for HTML_QuickForm which defines a grid of QuickForm elements
 *
 * PHP Versions 4 and 5
 *
 * @category HTML
 * @package  HTML_QuickForm_ElementGrid
 * @license  http://www.gnu.org/copyleft/lesser.html  LGPL
 * @author   Justin Patrin <papercrane@reversefold.com>
 * @version  $Id$
 */

require_once 'HTML/QuickForm/element.php';

/**
 * An HTML_QuickForm element which holds any number of other elements in a grid.
 * Used in DB_DataObject_FormBuilder for tripleLinks and crossLinks when there are
 * crossLinkExtraFields. This element type makes these grids of elements behave the
 * same as normal elements in the form. i.e. they will freeze correctly and get
 * values (defaults) set correctly.
 */
class HTML_QuickForm_ElementGrid extends HTML_QuickForm_element {

    /**
     * Array of arrays of HTML_QuickForm elements
     *
     * @var array
     */
    var $_rows = array();

    /**
     * Array of column names (strings)
     *
     * @var array
     */
    var $_columnNames = array();

    /**
     * Array of row names (strings)
     *
     * @var array
     */
    var $_rowNames = array();

    /**
     * Holds this element's name
     *
     * @var string
     */
    var $_name;

    /**
     * Holds a reference to the form for use when adding elements
     *
     * @var HTML_QuickForm
     */
    var $_form;

    /**
     * Holds options
     *
     * @var array
     */
    var $_options = array('actAsGroup' => false);

    /**
     * Constructor
     *
     * @param string name for the element
     * @param string label for the element
     */
    function HTML_QuickForm_ElementGrid($name = null, $label = null/*, $columnNames = null,
                                         $rowNames = null, $rows = null, $attributes = null*/,
                                         $options = null)
    {
        parent::HTML_QuickForm_element($name, $label);
        $this->updateAttributes(array('class' => 'elementGrid'));
        //$this->setRows($rows);
        //$this->setColumnNames($columnNames);
        //$this->setRowNames($rowNames);
        if (is_array($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
    }

    /**
     * Sets this element's name
     *
     * @param string name
     */
    function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Gets this element's name
     *
     * @return string name
     */
    function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the column names
     *
     * @param array array of column names (strings)
     */
    function setColumnNames($columnNames)
    {
        $this->_columnNames = $columnNames;
    }

    /**
     * Adds a column name
     *
     * @param string name of the column
     */
    function addColumnName($columnName)
    {
        $this->_columnNames[] = $columnName;
    }

    /**
     * Set the row names
     *
     * @param array array of row names (strings)
     */
    function setRowNames($rowNames)
    {
        $this->_rowNames = $rowNames;
    }

    /**
     * Sets the rows
     *
     * @param array array of HTML_QuickForm elements
     */
    function setRows(&$rows)
    {
        foreach (array_keys($rows) as $key) {
            $this->addRow($rows[$key]);
        }
    }

    /**
     * Adds a row to the grid
     *
     * @param array array of HTML_QuickForm elements
     * @param string name of the row
     */
    function addRow(&$row, $rowName = null)
    {
        $key = sizeof($this->_rows);
        $this->_rows[$key] = $row;

        //if updateValue has been called make sure to update the values of each added element
        foreach (array_keys($this->_rows[$key]) as $key2) {
            if (isset($this->_form)) {
                $this->_rows[$key][$key2]->onQuickFormEvent('updateValue', null, $this->_form);
            }
            if ($this->isFrozen()) {
                $this->_rows[$key][$key2]->freeze();
            }
        }
        if ($rowName !== null) {
            $this->addRowName($rowName);
        }
    }

    /**
     * Adds a row name
     *
     * @param string name of the row
     */
    function addRowName($rowName)
    {
        $this->_rowNames[] = $rowName;
    }

    /**
     * Freezes all elements in the grid
     */
    function freeze()
    {
        parent::freeze();
        foreach (array_keys($this->_rows) as $key) {
            foreach (array_keys($this->_rows[$key]) as $key2) {
                $this->_rows[$key][$key2]->freeze();
            }
        }
    }

    /**
     * Returns Html for the element
     * this method is fully overriden by the Moodle element grid Wrapper
     * as we cannot use pear HTML_Table class in Moodle.
     *
     * @access      public
     * @return      string
     */
    function toHtml()
    {
    	/*
        require_once 'HTML/Table.php';
        $table = new HTML_Table(null, 0, true);
        $table->updateAttributes($this->getAttributes());

        $tbody =& $table->getBody();
        $tbody->setAutoGrow(true);
        $tbody->setAutoFill('');

        $thead =& $table->getHeader();
        $thead->setAutoGrow(true);
        $thead->setAutoFill('');

        $col = 0;
        if ($this->_columnNames) {
            foreach ($this->_columnNames as $key => $value) {
                ++$col;
                $thead->setHeaderContents(0, $col, $value);
            }
            $thead->updateRowAttributes(0, array('class' => 'elementGridColumnLabel'), true);
        }

        $row = 0;
        foreach (array_keys($this->_rows) as $key) {
            $col = 0;
            $tbody->setHeaderContents($row, $col, isset($this->_rowNames[$key]) ? $this->_rowNames[$key] : '');
            foreach (array_keys($this->_rows[$key]) as $key2) {
                ++$col;
                $tbody->setCellContents($row, $col, $this->_rows[$key][$key2]->toHTML());
            }
            ++$row;
        }

        $tbody->updateColAttributes(0, array('class' => 'elementGridRowLabel'));
        return $table->toHTML();
        */

        /*include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer =& new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        $this->accept($renderer);
        return $renderer->toHtml();*/
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string  Name of event
     * @param     mixed   event arguments
     * @param     object  calling object
     * @access    public
     * @return    bool    true
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
        case 'updateValue':
            //store form for use in addRow
            $this->_form =& $caller;

            foreach (array_keys($this->_rows) as $key) {
                foreach (array_keys($this->_rows[$key]) as $key2) {
                    $this->_rows[$key][$key2]->onQuickFormEvent('updateValue', null, $caller);
                }
            }
            break;

        default:
            parent::onQuickFormEvent($event, $arg, $caller);
            break;
        }
        return true;
    }

    /**
     * Returns a 'safe' element's value
     *
     * @param  array   array of submitted values to search
     * @param  bool    whether to return the value as associative array
     * @access public
     * @return mixed
     */
    function exportValue(&$submitValues, $assoc = false)
    {
        if ($this->_options['actAsGroup']) {
            return parent::exportValue($submitValues, $assoc);
        }

        if ($assoc) {
            $values = array();
            foreach (array_keys($this->_rows) as $key) {
                foreach (array_keys($this->_rows[$key]) as $key2) {
                    $value = $this->_rows[$key][$key2]->exportValue($submitValues, true);
                    if (is_array($value)) {
                        $values = HTML_QuickForm::arrayMerge($values, $value);
                    } else {
                        $values[$this->_rows[$key][$key2]->getName()] = $value;
                    }
                }
            }
            return $values;
        } else {
            return null;
        }
    }

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    mixed
     */
    function getValue()
    {
        $values = array();
        foreach (array_keys($this->_rows) as $key) {
            foreach (array_keys($this->_rows[$key]) as $key2) {
                $values[$this->_rows[$key][$key2]->getName()] = $this->_rows[$key][$key2]->getValue();
            }
        }
        return $values;
    }
}

require_once 'HTML/QuickForm.php';

