<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_DataTable
 */

/**
 * Returns the equivalent PHP array of the DataTable.
 * You can specify in the constructor if you want the serialized version.
 * Please note that by default it will produce a flat version of the array.
 * See the method flatRender() for details.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Php extends Piwik_DataTable_Renderer
{
	public function __construct($table = null, $serialize = true)
	{
		parent::__construct($table);
		$this->setSerialize($serialize);
	}
	
	public function setSerialize( $bool )
	{
		$this->serialize = $bool;
	}
	
	public function __toString()
	{
		$data = $this->render();
		if(!is_string($data))
		{
			$data = serialize($data);
		}
		return $data;
	}
	
	/**
	 * Produces a flat php array from the DataTable, putting "columns" and "details" on the same level.
	 * 
	 * For example, when  a originalRender() would be 
	 * 	array( 'columns' => array( 'col1_name' => value1, 'col2_name' => value2 ),
	 * 	       'details' => array( 'detail1_name' => value_detail) )
	 * 
	 * a flatRender() is
	 * 	array( 'col1_name' => value1, 
	 * 	       'col2_name' => value2,
	 * 	       'detail1_name' => value_detail )
	 *  
	 * @return array Php array representing the 'flat' version of the datatable
	 *
	 */
	public function flatRender( $dataTable = null )
	{
		if(is_null($dataTable))
		{
			$dataTable = $this->table;
		}
		
		if($dataTable instanceof Piwik_DataTable_Array)
		{
			$flatArray = array();
			foreach($dataTable->getArray() as $keyName => $table)
			{
				$serializeSave = $this->serialize;
				$this->serialize = false;
				$flatArray[$keyName] = $this->flatRender($table);
				$this->serialize = $serializeSave;
			}
//			var_dump($flatArray);
		}
		
		// A DataTable_Simple is already flattened so no need to do some crazy stuff to convert it
		else if($dataTable instanceof Piwik_DataTable_Simple)
		{
			$flatArray = $this->renderSimpleTable($dataTable);
			
			// if we return only one numeric value then we print out the result in a simple <result> tag
			// keep it simple!
			if(count($flatArray) == 1)
			{
				$flatArray = current($flatArray);
			}
//			elseif(count($flatArray) == 0)
//			{
//				$flatArray = null;
//			}
			
		}
		// A normal DataTable needs to be handled specifically
		else
		{
			$array = $this->renderTable($dataTable);
			$flatArray = $this->flattenArray($array);
		}
		
		if($this->serialize)
		{
			$flatArray = serialize($flatArray);
		}
		
		return $flatArray;
	}
	
	protected function flattenArray($array)
	{
		$flatArray = array();
		foreach($array as $row)
		{
			$newRow = $row['columns'] + $row['details'];
			if(isset($row['idsubdatatable']))
			{
				$newRow += array('idsubdatatable' => $row['idsubdatatable']);
				if(isset($row['subtable']))
				{
					$newRow += array('subtable' => $this->flattenArray($row['subtable']) );
				}
			}
			$flatArray[] = $newRow;
		}		
		return $flatArray;
	}
	public function render( $dataTable = null)
	{
		if(is_null($dataTable))
		{
			$dataTable = $this->table;
		}
		$toReturn = $this->flatRender( $dataTable );
		
		if( false !== Piwik_Common::getRequestVar('prettyDisplay', false) )
		{
			if(!is_array($toReturn))
			{
				$toReturn = unserialize($toReturn);
			}
			$toReturn =  "<pre>" . var_export($toReturn, true ) . "</pre>";
		}
		return $toReturn;
	}
	
	public function originalRender()
	{
		if($this->table instanceof Piwik_DataTable_Simple)
		{
			$array = $this->renderSimpleTable($this->table);
		}
		else
		{
			$array = $this->renderTable($this->table, $doRenderSubTablesIfAvailable = false);
		}
				
		if($this->serialize)
		{
			$array = serialize($array);
		}
		
		return $array;
	}
	
	protected $serialize;
	
	
	protected function renderTable($table, $doRenderSubTablesIfAvailable = false)
	{
		$array = array();

		foreach($table->getRows() as $row)
		{
			$newRow = array(
				'columns' => $row->getColumns(),
				'details' => $row->getDetails(),
				'idsubdatatable' => $row->getIdSubDataTable(),
				);
			
			if($doRenderSubTablesIfAvailable
				&& $row->getIdSubDataTable() !== null)
			{
				try{
					$subTable =  $this->renderTable( Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable()));
					$newRow['subtable'] = $subTable;
				} catch (Exception $e) {
					// the subtables are not loaded we dont do anything 
				}
			}
			
			$array[] = $newRow;
		}
		return $array;
	}
	
	protected function renderSimpleTable($table)
	{
		$array = array();

		foreach($table->getRows() as $row)
		{
			$array[$row->getColumn('label')] = $row->getColumn('value');
		}
		
		return $array;
	}
}