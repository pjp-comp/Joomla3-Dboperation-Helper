<?php
//namespace etp;
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\Menu;
use Joomla\CMS\Menu\MenuItem;
class DbOperationsHelper extends JHelperContent
{

    public static function getInstance()
    {
        return new DbOperationsHelper();
    }
    public function deleteEntry($fieldName = null, $id = 0, $tableName = null){

        if(is_null($fieldName) || is_null($tableName)){
            return false;
        }

        $db = JFactory::getDbo();

        $query = $db->getQuery(true);


        if(is_array($id) && !empty($id)){
            $idsComma = implode(',',$id);
            $conditions = array(
                $db->quoteName($fieldName) . " IN (".$idsComma.")"
            );
        }else{
            $conditions = array(
                $db->quoteName($fieldName) . " = " . $id
            );
        }


//        $conditions = array(
//            $db->quoteName($fieldName) . " = " . $id
//        );

        $query->delete($db->quoteName($tableName));
        $query->where($conditions);

        $db->setQuery($query);
        return $db->execute();
    }

    public function getEntriesObj($tableName , $dbOptions = [] , $byKey = null){

           $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            $select = array('*');

            $query
                ->select($select)
                ->from($db->quoteName($tableName));

               if(!empty($dbOptions)){
                if(isset($dbOptions['where']) && !empty($dbOptions['where'])){

                    foreach ($dbOptions['where'] as $conKey=>$conV){
                        if(is_string($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$db->quote($conV));
                        }elseif(is_numeric($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$conV);
                        }elseif(is_array($conV) && !empty($conV)){
                            $query->where($db->quoteName($conKey) ." IN (" .implode(',',$conV).")");
                        }else{
                            // $query->where($db->quoteName($conKey) ." = " .$conV);
                        }
                    }
                }
//                if(isset($dbOptions['group']) && $dbOptions['group'] != ""){
//                        $query->group($db->quote($dbOptions['group']));
//                }
                if(isset($dbOptions['order']) && $dbOptions['order'] != ""){
                        $query->order($dbOptions['order']);
                }
                if(isset($dbOptions['limit']) && $dbOptions['limit'] != ""){
                        $query->setLimit($dbOptions['limit']);
                }

            }

            $db->setQuery((string) $query);
            return (is_null($byKey)) ? $db->loadObjectList() : $db->loadObjectList($byKey);
    }

        public function getEntriesArray($tableName , $dbOptions = [] , $byKey = null){

           $db    = JFactory::getDBO();
            $query = $db->getQuery(true);

            $select = array('*');

            $query
                ->select($select)
                ->from($db->quoteName($tableName));

            if(!empty($dbOptions)){
                if(isset($dbOptions['where']) && !empty($dbOptions['where'])){

                    foreach ($dbOptions['where'] as $conKey=>$conV){
                        if(is_string($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$db->quote($conV));
                        }elseif(is_numeric($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$conV);
                        }elseif(is_array($conV) && !empty($conV)){
                            $query->where($db->quoteName($conKey) ." IN (" .implode(',',$conV).")");
                        }else{
                            // $query->where($db->quoteName($conKey) ." = " .$conV);
                        }
                    }
                }
//                if(isset($dbOptions['group']) && $dbOptions['group'] != ""){
//                        $query->group($db->quote($dbOptions['group']));
//                }
                if(isset($dbOptions['order']) && $dbOptions['order'] != ""){
                        $query->order($dbOptions['order']);
                }
                if(isset($dbOptions['limit']) && $dbOptions['limit'] != ""){
                        $query->setLimit($dbOptions['limit']);
                }

            }
            $db->setQuery((string) $query);
            return (is_null($byKey)) ?  $db->loadAssocList() : $db->loadAssocList($byKey);
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
            try{

                $db->setQuery((string) $query);
                return $db->loadAssoc();
            }catch(Exception $e){
                return [];
            }
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

    public function updateDataWhere($dbOptions = [], $data = array(), $tableName = null ){

            if(empty($data) || is_null($tableName)){
                return false;
            }

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $fields = array();

            // $updateId = (is_integer($data[$updateBy])) ? (int)$data[$updateBy] : $db->quote($data[$updateBy]);

            // unset($data[$updateBy]);

            foreach ($data as $column=>$value){

                if(is_numeric($value)){
                    $field = $value;
                }else{
                    $field = $db->quote($value);
                }
                $field = $db->quoteName($column) . ' = ' . $field;

                array_push($fields, $field);
            }

            $conditions = [];
            if(!empty($dbOptions)){
                if(isset($dbOptions['where']) && !empty($dbOptions['where'])){

                    foreach ($dbOptions['where'] as $conKey=>$conV){
                        if(is_string($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$db->quote($conV));
                        }elseif(is_numeric($conV)){
                            $query->where($db->quoteName($conKey) ." = " .$conV);
                        }elseif(is_array($conV) && !empty($conV)){
                            $query->where($db->quoteName($conKey) ." IN (" .implode(',',$conV).")");
                        }else{
                            // $query->where($db->quoteName($conKey) ." = " .$conV);
                        }
                    }
                }
            }
//            $conditions = array(
//                $db->quoteName($updateBy) . ' = '.$updateId,
//            );

            $query->update($db->quoteName($tableName))->set($fields)->where($conditions);

            $db->setQuery($query);
            return $db->execute();

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

        if(is_null($activeId) || $activeId == 0){
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

    private function makerequest($url,$type="GET",$data = null){

        $curl = curl_init();
        // $url = $this->accountingAppHost.$url;
        if($type == "GET"){
            $url .= '?'.http_build_query($data);
        }
        else{
            $post_data = http_build_query($data);
        }

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 36,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => $post_data,
            // CURLOPT_HTTPHEADER => array(
            //     "Authorization: ".implode('/',[$this->authtoken,$this->accountlabel]),
            //     "Cache-Control: no-cache"
            // ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
            return false;
        } else {

            return $response;

        }
    }

    public function uploadFile($attachedFiles, $destination) {

		if (!isset($attachedFiles['name']) || empty($attachedFiles['name']) ||  (!isset($destination) && $destination != "" && is_dir($destination))) {
            die('invalid name/no file/destination');
			return false;
		}
		$application = JFactory::getApplication();


        $dest = $destination;
//        $dest = JPATH_COMPONENT_ADMINISTRATOR."/assets/images/investors";
//		$dest = JRoute::_(JPATH_SITE . "/media/com_etp/company/");
		// echo $dest; die('link');



        $allowExtention = $this->checkExtension($attachedFiles['name']);
        $isError = $this->checkError($attachedFiles['error']);
        $allowSize = $this->checkSize($attachedFiles['size']);

        if (!$allowExtention || $isError || !$allowSize) {
            $application->enqueueMessage(JText::_('FEW FILES ARE INVALID'), 'error');
            return "";
        }

        $filename = JFile::makeSafe($attachedFiles['name']);
        $filename = round(microtime('true')*1000) . $filename;

        $file_tmp_name = $attachedFiles['tmp_name'];

        if (JFile::upload($file_tmp_name, $dest.'/'.$filename)) {
            return $filename;
        } else {
            return "";
        }

	}
	private function checkError($fileError) {
		//any errors the server registered on uploading
		// $fileError = $_FILES[$fieldName]['error'];
		if ($fileError > 0) {
			switch ($fileError) {
			case 1:
				echo JText::_('FILE TO LARGE THAN PHP INI ALLOWS');
				return true;

			case 2:
				echo JText::_('FILE TO LARGE THAN HTML FORM ALLOWS');
				return true;

			case 3:
				echo JText::_('ERROR PARTIAL UPLOAD');
				return true;

			case 4:
				echo JText::_('ERROR NO FILE');
				return true;
			}
		}
		return false;
	}
	private function checkSize($fileSize) {
		if ($fileSize > 2000000) {
			echo JText::_('FILE BIGGER THAN 2MB');
			return false;
		}
		return true;
	}
	private function checkExtension($fileName)
    {
        //check the file extension is ok
        // $fileName = $_FILES[$fieldName]['name'];

        $uploadedFileExtension = JFile::getExt($fileName);
        // echo $uploadedFileExtension;die;

        $validFileExts = explode(',', 'jpeg,jpg,png');

        if (!in_array($uploadedFileExtension, $validFileExts)) {
            return false;
        } else {
            return true;
        }

        //assume the extension is false until we know its ok
        $extOk = false;

        //go through every ok extension, if the ok extension matches the file extension (case insensitive)
        //then the file extension is ok
        foreach ($validFileExts as $key => $value) {
            if (preg_match("/$value/i", $uploadedFileExtension)) {
                $extOk = true;
            }
        }

        if ($extOk == false) {
            echo JText::_('INVALID EXTENSION');
            return;
        }
    }

    // need to impove
      public function upload()
      {
          // die('fgdg');
          $result = ['status' => "error", "msg" => "invalid"];
          $targetPath = JPATH_ROOT . '/images/etp/';

          if (!is_dir(JPATH_ROOT . '/images/etp')) {
              mkdir(JPATH_ROOT . '/images/etp', 0755);
          }

          if (!empty($_FILES)) {

              $tempFile = $_FILES['file']['tmp_name'];          //3

              $fileName = time() . str_replace(array('\'', '"', ',', ';', '<', '>', '@'), '', $_FILES['file']['name']);


              $targetFile = $targetPath . $fileName;  //5

              if (move_uploaded_file($tempFile, $targetFile)) {
                  $targetFile = JURI::root() . 'images/etp/' . $fileName;
                  $result['status'] = "success";
                  $result['msg'] = $fileName;
                  $result['filepath'] = $fileName;
                  echo json_encode($result);
              } else {
                  $result['msg'] = "Problem while uploading.";
                  echo json_encode($result);
              }
          } else {
              $result['msg'] = "File is not there.";
              echo json_encode($result);
          }
          die;
      }


    // pass string or array
    public function make_log($arMsg){
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
        else{   //concatenate msg with datetime
            $stEntry.=$arLogData['event_datetime']." ".$arMsg."\n";
        }

        //create file with current date name
        $stCurLogFileName='ppt_log'.date('Ymd').'.txt';


        //open the file append mode,dats the log file will create day wise

        $fHandler=fopen(JPATH_COMPONENT."/assets/logs/".$stCurLogFileName,'a+');

        //write the info into the file
        fwrite($fHandler,$stEntry);
        //close handler
        fclose($fHandler);
    }
         /*
     *  set associate $data ..its key will be onsider as cookie key and its value as cookie value
     */
    public function setCookie($data){

        if(empty($data)){
            return fasle;
        }

        $cookieName = key($data);
        $app = JFactory::getApplication();
        $value = $app->input->cookie->get($cookieName, null);


        // Get the cookie
//        $value = $app->input->cookie->get($cookieName, null);

        // If there's no cookie value, manually set it

          if ($value == null || $data[$cookieName] != $value) {
             $value = $data[$cookieName];
          }else{
              return false;
          }

        // Set the cookie
        $time = time() + 604800; // 1 week
        $app->input->cookie->set($cookieName, $value, $time, $app->get('cookie_path', '/'), $app->get('cookie_domain'), $app->isSSLConnection());
//        $app->input->cookie->set($cookieName, $value, $time, '/');
        return true;
    }


    public function readCookies($cookieName){
        $app = JFactory::getApplication();
        $value = $app->input->cookie->get($cookieName, null);
        return $value;
    }
    function getModelAdmin($component, $name = 'Custom', $prefix = 'CustomModel')
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables', $prefix);
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/models', $prefix);
        $model = JModelLegacy::getInstance($name, $prefix, array('ignore_request' => true));
        return $model;
    }

//   private $deleteDirectory = null;
//   https://www.php.net/manual/en/class.closure.php
// it will empty and remove dir.

    function deleteDirectory($path = ""){
        $resource = opendir($path);
        while (($item = readdir($resource)) !== false) {
            if ($item !== "." && $item !== "..") {
                if (is_dir($path . "/" . $item)) {
                    self::deleteDirectory($path . "/" . $item);
                } else {
                    unlink($path . "/" . $item);
                }
            }
        }
        closedir($resource);
        rmdir($path);
    }

    public function csvToArray($file) {

        $rows = array();
        $headers = array();
        if (file_exists($file) && is_readable($file)) {
            $handle = fopen($file, 'r');
            while (!feof($handle)) {
                $row = fgetcsv($handle, 10240, ',', '"');
//                $row = array_map("utf8_encode", $row1);
                if (empty($headers)){
                    // $row = array_map("utf8_encode", $row);
                    $row = array_map('strtolower', $row);

                    $headers = $row;
                }
                else if (is_array($row)) {
                    array_splice($row, count($headers));
                    $rows[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        } else {
            throw new Exception($file . ' doesn`t exist or is not readable.');
        }
        return $rows;
    }

    public function createPossibilities($quetions, $limit){
        $result = [];

        foreach ($quetions as $qKey=>$quetion){

            for($count = 1; $count <= $quetion['possibility'];$count++){
                $newQ = [];
                $newQ = $quetion;
                $newQ['ques'] = $newQ['ques'].str_repeat(' ',$count);
                $result[] = $newQ;
            }
        }



        $additionalGenerate = $this->repeatQuetions($result, $limit);

        $results = array_slice($additionalGenerate, 0, $limit);
        shuffle($results);

        return $results;
    }

    public function repeatQuetions($quetions, $limit){
     

        $original = $quetions;

        $additions = [];
        if(!empty($quetions) && $limit > 0 && $limit > count($quetions)){
            // $diff = ceil($limit/count($quetions));
            $diff = $limit - count($quetions);


            while($diff > 0){

                $key = array_rand($quetions, 1);
                $newArr = [];
                $quetions[$key]['ques'] = $quetions[$key]['ques'].' '; // adding space avoid repeative quetions. --patiyu
                $newArr = $quetions[$key];
                $additions[] = $newArr;
                $diff--;
            }

        }


        $final = array_merge($original, $additions);

        shuffle($final);
        return $final;
    }

    public function shuffleOptions($questions, $attachTags = false){

        if($attachTags){
            $dbo = DbOperationsHelper::getInstance();
            $tags = $dbo->getEntriesArray('#__osce_tags',[],'id');
        }

        $result = [];
        foreach ($questions as $que){

            if($attachTags){

                $availTags = json_decode($que['tag_id']);

                $catLabel = (count($availTags) > 1) ? "Categories":"Category";

                $attachedTagNams = implode(', ',array_map(function ($tagId)use($tags) {
                    return (isset($tags[$tagId])) ? ucfirst($tags[$tagId]['title']) : 'N/A';
                },$availTags));

//                $que['ques'] = $que['ques'] .'( '.$catLabel.': '.$attachedTagNams.')';
                $que['ques'] = $que['ques'] .'<br><span class="que_category">('.$catLabel.': '.$attachedTagNams.')</span>';
                $que['ques'] = htmlentities($que['ques']);
            }


            $osce = [];
            if(isset($que['text']) && isset($que['answers']) && isset($que['correct'])){
                    $correctAns = $que['answers'][$que['correct']];
                    $osce['text'] = $que['text'];
                    $options = $que['answers'];
            }else{

                $correctAns = $que['opt'.$que['correct_ans']];
                $osce['text'] = $que['ques'];
                $options = [];
                for ($i=1 ; $i<=6; $i++){
                    if(isset($que['opt'.$i]) && $que['opt'.$i] != ""){
                        $options[] = $que['opt'.$i];
                    }
                }

            }


            shuffle($options);
            $osce['answers'] = $options;
            $osce['correct'] = array_search($correctAns, $osce['answers']);

            $result[] = $osce;
        }
        return $result;
    }

    function removeDuplicateOnKey($key,$data){

        $_data = array();

        foreach ($data as $v) {
          if (isset($_data[$v[$key]])) {
            // found duplicate
            continue;
          }
          // remember unique item
          $_data[$v[$key]] = $v;
        }
        // if you need a zero-based array
        // otherwise work with $_data
        $data = array_values($_data);
        return $data;
    }

    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @author    Webkul
     * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     *
     * getModelAdmin function
     *
     * @param [String] $component name of component
     * @param string $name name of model
     * @param string $prefix
     * @return Object
     */
    function getModelAdmin($component, $name = 'Custom', $prefix = 'CustomModel')
    {
        if (!isset($component)) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_ERROR_MSG"), 'error');
            return false;
        }
        $path=JPATH_ADMINISTRATOR . '/components/'.$component.'/models/';
        JModelLegacy::addIncludePath($path);
        require_once $path.strtolower($name).'.php';
        $model = JModelLegacy::getInstance($name, $prefix);
        // If the model is not loaded then $model will get false
        if ($model == false) {
            $class=$prefix.$name;
            // initilize the model
            new $class();
            $model = JModelLegacy::getInstance($name, $prefix);
        }
        return $model;
    }

    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @author    Webkul
     * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     *
     *
     * getModelSite function
     *
     * @param [String] $component name of component
     * @param string $name name of model
     * @param string $prefix
     * @return Object
     */
    function getModelSite($component, $name = 'Custom', $prefix = 'CustomModel')
    {
        if (!isset($component)) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_ERROR_MSG"), 'error');
            return false;
        }
        $path=JPATH_SITE . '/components/'.$component.'/models/';
        JModelLegacy::addIncludePath($path);
        require_once $path.strtolower($name).'.php';
        $model = JModelLegacy::getInstance($name, $prefix);
        // If the model is not loaded then $model will get false
        if ($model == false) {
            $class=$prefix.$name;
            // initilize the model
            new $class();
            $model = JModelLegacy::getInstance($name, $prefix);
        }
        return $model;
    }

}
