<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class DbOperationsHelper extends JHelperContent
{

    public function deleteEntry($fieldName = null, $id = 0, $tableName = null){

        if(is_null($fieldName) || is_null($tableName) || !is_numeric($id)){
            return false;
        }

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName($fieldName) . " = " . $id
        );

        $query->delete($db->quoteName($tableName));
        $query->where($conditions);

        $db->setQuery($query);
        return $db->execute();
    }
    public function getEntryObj($id = 0, $tableName = null, $fieldName = "id"){

        /*if(!is_numeric($id) || is_null($tableName) || $fieldName == ""){
            return array();
        }*/

           $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            $select = array('*');

            $query
                ->select($select)
                ->from($db->quoteName($tableName));

            if(is_string($id)){
                $query->where($db->quoteName($fieldName) ." = " .$db->quote($id));
            }else{
                $query->where($db->quoteName($fieldName) ." = " .$id);
            }
            $db->setQuery((string) $query);
            return $db->loadObject();
    }

    public function getEntryArray($id = 0, $tableName = null, $fieldName = "id"){

        /*if(!is_numeric($id) || is_null($tableName) || $fieldName == ""){
            return array();
        }*/

           $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            $select = array('*');

            $query
                ->select($select)
                ->from($db->quoteName($tableName));

            if(is_string($id)){
                $query->where($db->quoteName($fieldName) ." = " .$db->quote($id));
            }else{
                $query->where($db->quoteName($fieldName) ." = " .$id);
            }
            $db->setQuery((string) $query);
            return $db->loadAssoc();
    }

    public function makeEntry($data, $tableName = null){

        if(empty($data) || is_null($tableName)){
            return false;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $columns = array();
        $values = array();

        foreach ($data as $column => $value){

            if(is_numeric($value)){
                array_push($values, $value);
            }else{
                array_push($values, $db->quote($value));
            }
            array_push($columns, $column);
        }

        $query
            ->insert($db->quoteName($tableName))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        if($db->execute()){
            return $db->insertid();
        }
        return false;
    }
    // insert bulk data in table
	public function insertBulkData($tableName, $columns, $values){

		$db = JFactory::getDbo();

		try{
			$db->transactionStart();
			$query = $db->getQuery(true);

			// apply quote on non numeric value and convert data array to row
			$data = $this->arrayToRowQuoted($db, $values);

//			 echo $tableName ."<br>";
//			 print_r($columns);
//			 print_r($data);
//			 die;

			// Prepare the insert query.
			$query
			    ->insert($db->quoteName($tableName))
			    ->columns($db->quoteName($columns))
			    ->values($data);

			// Set the query using our newly populated query object and execute it.

			// echo $query.';';
			$db->setQuery($query);
			$isInserted = $db->execute();
			$db->transactionCommit();

		}catch (Exception $e) {

			// die($e);
		    // catch any database errors.
		    $db->transactionRollback();
		    JErrorPage::render($e);
		    return false;
		}
		return ($isInserted) ? true : false ;
	}

	public function updateData($updateBy = "id", $data = array(), $tableName = null ){

            if(empty($data) || is_null($tableName)){
                return false;
            }

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $fields = array();

            $updateId = (is_integer($data[$updateBy])) ? (int)$data[$updateBy] : $db->quote($data[$updateBy]);

            unset($data[$updateBy]);

            foreach ($data as $column=>$value){

                if(is_numeric($value)){
                    $field = $value;
                }else{
                    $field = $db->quote($value);
                }
                $field = $db->quoteName($column) . ' = ' . $field;

                array_push($fields, $field);
            }

            $conditions = array(
                $db->quoteName($updateBy) . ' = '.$updateId,
            );

            $query->update($db->quoteName($tableName))->set($fields)->where($conditions);

            $db->setQuery($query);
            return $db->execute();

    }

    // apply quote on non numeric value and convert data array to row
	private function arrayToRowQuoted($db, $values){
		$data = array();
			foreach ($values as $rowValues) {

				$quoteValues = array();

				foreach ($rowValues as $rowValue) {

					// no quote for float values
					if(!is_numeric($rowValue)){
						$rowValue = $db->quote($rowValue);
					}

					$quoteValues[] = $rowValue;
				}

				$data[] = implode(",", $quoteValues);
				// prin'before to insert');
			}
		return $data;
	}

    public function prepareOptions($optionList = array(), $optionKey = "id", $optionName = "name", $activeId = null, $enableSelect = true, $customSelectMsg = "Please Select"){

        if(is_null($activeId)){
            $selected = "selected";
        }

        if($enableSelect){
            $options = "<option value='' $selected>{$customSelectMsg}</option>";
        }


       foreach ($optionList as $option){

            $selected = "";
            if(!is_null($activeId) && $activeId == $option->$optionKey){
                $selected = " selected ";
            }
            $options .= "<option value='{$option->$optionKey}' $selected>{$option->$optionName}</option>";
       }

       return $options;

    }



    // pass string or array
    public static function wine_log($arMsg){
        //define empty string
        $stEntry="";
        //get the event occur date time,when it will happened
        $arLogData['event_datetime']='['.date('D Y-m-d h:i:s A').'] [client '.$_SERVER['REMOTE_ADDR'].']';

        //if message is array type

        if(is_array($arMsg) || is_object($arMsg)){
            $arMsg = (array)$arMsg;
            //concatenate msg with datetime
            foreach($arMsg as $k=>$msg){

                if(is_array($msg)){$msg = json_encode($msg);}
                $stEntry.=$arLogData['event_datetime']." (".$k."=)".$msg."\n";
            }
        }
        else
        {   //concatenate msg with datetime

            $stEntry.=$arLogData['event_datetime']." ".$arMsg."\n";
        }

        //create file with current date name
        $stCurLogFileName='res_log'.date('Ymd').'.txt';


        //open the file append mode,dats the log file will create day wise

        $fHandler=fopen(JPATH_ROOT."/components/com_laitqb/assets/logs/".$stCurLogFileName,'a+');

        //write the info into the file
        fwrite($fHandler,$stEntry);
        //close handler
        fclose($fHandler);
    }

}
