<?php

/**
 * @author Carlos Cortez
 * @package DataTypes
 */
class DataType_DatabasePull extends DataTypePlugin {
	protected $isEnabled = true;
	protected $dataTypeName = "Database Pull";
	protected $hasHelpDialog = true;
	protected $dataTypeFieldGroup = "other";
	protected $dataTypeFieldGroupOrder = 50;


	public function generate($generator, $generationContextData) {
		$options = $generationContextData["generationOptions"];
		$rowNum  = $generationContextData["rowNum"];
		$numValues = count($options["values"]);
		if ($numValues == 1) {
			$value = $options["values"][0];
		} else {
			$itemIndex = floor(($rowNum-1) / $options["loopCount"]);
			if ($itemIndex > ($numValues - 1)) {
				$itemIndex = ($itemIndex % $numValues);
			}
			$value = $options["values"][$itemIndex];
		}
		
		return array(
			"display" => $value
		);
	}

	
/* getDataTypeMetadata gives metadata about the data type for correct generation of the SQL statement*/
	
	public function getDataTypeMetadata() {
		return array(
			"SQLField" => "varchar(255)",
			"SQLField_Oracle" => "varchar2(255)",
			"SQLField_MSSQL" => "VARCHAR(255) NULL"
		);
	}

	public function getRowGenerationOptionsUI($generator, $postdata, $colNum, $numCols) {
		
		//Check if parameters have been set
		//Change isset to empty + ||
		if (!isset($postdata["dtServerName_$colNum"], $postdata["dtUsername_$colNum"], $postdata["dtPassword_$colNum"], $postdata["dtTable_$colNum"], $postdata["dtColumn_$colNum"])) {
			return false;
		}
	
		$servername = $postdata["dtServerName_$colNum"];
		$username = $postdata["dtUsername_$colNum"];
		$password = $postdata["dtPassword_$colNum"];
		$database = $postdata["dtDatabase_$colNum"];
		$table = $postdata["dtTable_$colNum"];
		$column = $postdata["dtColumn_$colNum"];
		

		
		// Create connection
		$conn = new mysqli($servername, $username, $password, $database);

		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} 

			
		$sql = "SELECT ".$column." FROM ".$table;
		
		$result = $conn->query($sql);

		
		if ($result->num_rows > 0) {
			// output data of each row
			$columnData = array();
			$row = $result->fetch_assoc();
			do{
				$columnData[] = $row[$column];
				$row = $result->fetch_assoc();
			}while(!empty($row));
		} else {
			echo "0 results in the column-table-database configuration";
		}

			

		$conn->close();

		
		$options = array(
			"loopCount" => 1,
			"values"    => $columnData
		);

		return $options;
	}

	
	
	public function getExampleColumnHTML() {
		$L = Core::$language->getCurrentLanguageStrings();
		return $L["see_help_dialog"];
	}

	public function getOptionsColumnHTML() {
		$html =<<<EOF
<table cellspacing="0" cellpadding="0" width="260">
	<tr>
		<td>{$this->L["servername"]}</td>
		<td><input type="text" name="dtServerName_%ROW%" id="dtServerName_%ROW%" style="width: 100%" value="localhost" /></td>
	</tr>
	<tr>
		<td>{$this->L["username"]}</td>
		<td><input name="dtUsername_%ROW%" id="dtUsername_%ROW%" style="width: 100%" value=""/></td>
	</tr>
	<tr>
		<td>{$this->L["password"]}</td>
		<td><input name="dtPassword_%ROW%" id="dtPassword_%ROW%" style="width: 100%" value=""/></td>
	</tr>
	<tr>
		<td>{$this->L["database"]}</td>
		<td><input name="dtDatabase_%ROW%" id="dtDatabase_%ROW%" style="width: 100%" value="" /></td>
	</tr>
	<tr>
		<td>{$this->L["table"]}</td>
		<td><input name="dtTable_%ROW%" id="dtTable_%ROW%" style="width: 100%" value="" /></td>
	</tr>
	<tr>
		<td>{$this->L["column"]}</td>
		<td><input name="dtColumn_%ROW%" id="dtColumn_%ROW%" style="width: 100%" value="" /></td>
	</tr>
</table>
EOF;
		return $html;
	}


	public function getHelpHTML() {
		return "<p>{$this->L["help"]}</p>";
	}	
	
	#TODO: edit API function
/*	public function getRowGenerationOptionsAPI($generator, $json, $numCols) {
		/*if ($json->settings->loopCount <= 0) {
			return false;
		}    

		$options = array(
			"servername" => $json->settings->servername,
			"username"    => $json->settings->username,
			"password"    => $json->settings->password,
			"table"    => $json->settings->table
			"column"    => $json->settings->column)
			
			
		);
		return $options;
	}
*/
	
	
	

}
