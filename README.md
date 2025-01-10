# joomla_quick_dboperation

Useful easy and fast solution for your db operations.
it will work for noth joomla3 & joomla4.
You need to import this helper in your controller/model/anyWhere where you going to perform db operations.

How to use it?

1. create an object of DbOperationsHelper. or get an instance using static method DbOperationsHelper::getInstance()
   
    `$dboperation = DbOperationsHelper::getInstance();`

3. if you looking to insert data to your table
    // prepare all required fields in array. Here key is your table column name. no need to give primery_key.
   
    $data = ['first_name'=>'foo', 'last_name'=>'faa'];

    $result = $dboperation->makeEntry($data, "#__table_name"); // this will store data in your table.

5. How to store bulk data?

   $data = [
         ['foo1', 'faa1'],
         ['foo2', 'faa2'],
         ['foo3', 'faa3']
       ];

   $columns = ['first_name', 'last_name'];

   $result = $dboperation->insertBulkData("#__table_name", $columns, $data);

7. Fetch single data by primary key

   $id = 6;

   $result = $dboperation->getEntryObj($id, "#__table_name", "id"); if your are fetching by id in object format

   $result = $dboperation->getEntryArray($id, "#__table_name", "id"); if your are fetching by id in array format

9. fetch bulk data: this will fetch whose firat name is faa1. and result data is associate by id


   $result = $dboperation->getEntriesArray("#__table_name", ['where'=>['first_name'=>'foo1']], 'id');

   $result = $dboperation->getEntriesObj("#__table_name", ['where'=>['first_name'=>'foo1']], 'id'); // this will return data by id
   
11. update data

    // this will update whose first_name is faa1.
  
    $result = $dboperation->updateDataWhere(['where'=>['first_name'=>'foo1']], ['first_name'=>'foo_updated', 'last_name'=>'faa_updated'], "#__table_name")

    // user can also use IN conditoin
  
   $result = $dboperation->updateDataWhere(['where'=>['first_name IN'=>['foo1', 'foo2']]], ['first_name'=>'foo_updated', 'last_name'=>'faa_updated'], "#__table_name")

   
   // update by id. so while updating it will remove $data['id'] while updating.
   
   $data = ['id'=>3, 'first_name'=>'foo_updated', 'last_name'=>'faa_updated'];
   
   $result = $dboperation->updateData('id',$data , "#__table_name");

11. delete entry

    $result = $dboperation->deleteEntry('first_name', 'foo1', "#__table_name");

13. There are many useful functions available and you may add or modify.

    example :

    truncate('table_name')

    makerequest('url', 'get') // to make a curl request

    uploadFile, makelog, setCookie, readCookies, csvToArray(file_path), 
    







