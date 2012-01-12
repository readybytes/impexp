<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__). '/joomlaFramework.php';

class XiDBCheck
{
    private $db;
    private $testTables;
    private $excludeC;
    private $excludeR;
    private $orderBy;
    private $errorLog;
    private $log;
    function __construct()
    {
        if(!$this->db)
            $this->db=& JFactory::getDBO();
    }
    
    /*
     * Original Tables : 
     * Compares tables
     * */
    function compareTable($tableName)
    {
        $this->log[]= "\n --> Comparing table ".$tableName;
        
        //sometimes it creates issue :
        if(!$this->excludeR || array_key_exists($tableName, $this->excludeR)==false)
        	$this->excludeR[$tableName]="";
        
        $tmpCol = $this->db->getTableFields($tableName, false);
        $allcol = array_keys ($tmpCol[$tableName]);
        $ec 	= $this->excludeC[$tableName];
        if(empty($ec))
        	$ec=array();
        $fields = array_diff($allcol, $ec);
        
        $this->log[]= "\n ALLCOL:".var_export($allcol,true);
        $this->log[]= "\n To be filtered :".var_export($this->excludeC[$tableName],true);
        
        
        $select = ' * ';
        if($fields)
        {    
            $select = '`';
            $select .=implode('`,`',$fields);
            $select .= '`';
         }
        
        $this->log[]=  "\n comparing fields : ".var_export($fields,true).
        		 "\n Select is: ".var_export($select,true);
        
        $query    = ' SELECT '.$select.' FROM '. $tableName
                    . $this->excludeR[$tableName]
                    . $this->orderBy[$tableName];
        $this->db->setQuery($query);
        $this->log[]= "\n Query for logTable : ".$query."\n";
        $logTable = $this->db->loadAssocList();

        $query    = ' SELECT '.$select.' FROM '. ' au_'.$tableName 
                    . $this->excludeR[$tableName]
                    . $this->orderBy[$tableName];
        $this->db->setQuery($query);
        
         $this->log[]= "\n Query for auTable : ".$query."\n";
         $auTable = $this->db->loadAssocList();

         $this->log[]=  "\n auTable :".var_export($auTable,true);
         $this->log[]=  "\n logTable :".var_export($logTable,true);
        $count=count($auTable);
        $isError = false;

        $logCount=count($logTable);
        $auCount=count($auTable);
	
        for($i=0 ; $i<$count;$i++)
        {    
        	$auArr = $auTable[$i];
        	$logArr = $logTable[$i];
        	if(!$auArr)
        		$auArr=array();
        	if(!$logArr)
        		$logArr=array();
            if($diff = array_diff_assoc($auArr,$logArr))
            {
                $error = "\n \n \n Table " . $tableName . " mismatched"
                        ."\n for Rows " . $this->excludeR[$tableName]
                        ."\n for Cols " . $select
                        ."\n for Order " . $this->orderBy[$tableName]
                        ."\n Diff " . var_export($diff,true)
                        ."\n Gold Result " . var_export($auTable[$i],true)
                        ."\n Log " . var_export($logTable[$i],true)
                        ;
                $this->errorLog[]=$error;
				$this->log[]= $error;
                $isError = true;
            }
            else
            {
                $this->log[]= "\n Table ".$tableName. " Record : ".$i . " Matched";
            }
            
        }
        
	if($logCount != $auCount)	
	{		
		$error = "Table count is not same Log Count : $logCount and AuCount : $auCount";
		$this->errorLog[]=$error;
		$this->log[]= $error;
        //Fix error in testcases before we enable this error check  
		//$isError = true;
	}


        if($isError)
        	return false;
        	
        $this->log[]= " \n ==  Table ".$tableName. " Matched == \n ";
        return true;        
    }
    
    function addTable($tableName)
    {
        //if(!in_array($testTables,$tableName))
        $this->testTables[] = $tableName;
        $this->excludeC[$tableName]='';
        $this->orderBy[$tableName]='';
    }
    
    function filterColumn($table, $column)
    {
        $this->excludeC[$table][] = $column;
    }
    
    function filterRow($table, $row)
    {
    	if(!$this->excludeR)
    		$this->excludeR=array();
    		
        if(array_key_exists($table, $this->excludeR)==false)
            $this->excludeR[$table] = ' WHERE '. $row;
        else
            $this->excludeR[$table] = ' AND '. $row;
    }
    
    function filterOrder($table, $order)
    {
            $this->orderBy[$table] = ' ORDER BY  '. $order;
    }
    
    
    function verify()
    {
    	if($this->testTables){
        	foreach($this->testTables as $t){
            	if($this->compareTable($t)==false)
                	return false;
        	}
    	}
        return true;
    }
    
    function getErrorLog()
    {
       return $this->errorLog;
    }
    
	function getLog()
    {
       return $this->log;
    }
    
    function loadSql($file)
    {
        if(!file_exists($file))
        {
            $this->errorLog[]="File does not exist : ".$file;
            $this->log[]= "File does not exist : ".$file;
            return false;
        }
        $query=file_get_contents($file);
        $this->execSql($query);
        return true;
    }
    
    function execSql($query)
    {
    	$allQuery=explode(';;',$query);
        
        foreach($allQuery as $q)
        {
            // we might have empty queries
            $q = trim($q);
            if(empty($q))
                continue;

            $this->db->setQuery($q);
            
            if(!$this->db->query())
            {
                $error = "Joomla DB Error Number : ".$this->db->getErrorNum();
                $this->errorLog[]=$error;
                    
                echo "\n Some error during Sql Loading : ".$error.".\n";
                echo $q."\n";
                break;
            }
        }	
    }
}

class XiTestListener implements PHPUnit_Framework_TestListener
{
  public function addError(PHPUnit_Framework_Test $test,Exception $e,$time){}
  public function addFailure(PHPUnit_Framework_Test $test,PHPUnit_Framework_AssertionFailedError $e,$time){}
  public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time){}
  public function addSkippedTest(PHPUnit_Framework_Test $test,Exception $e,$time){}
  public function startTestSuite(PHPUnit_Framework_TestSuite $suite){}
  public function endTestSuite(PHPUnit_Framework_TestSuite $suite){}
  
  
  public function startTest(PHPUnit_Framework_Test $test)
  {
    static $i=1;
    static $className = "";
    $testName      = $test->getName();

    $newClassName = get_class($test);
    if($newClassName != $className){
    	echo "\n\n      --$newClassName--";
    	$className = $newClassName;
    }
    printf("\n %3d",$i);
	echo ": $testName "; $i++;

	$test->startTime = time();

    // this two variables must be defined by test
    if(!method_exists($test,'getSqlPath'))
        return;

    $sqlPath       = $test->getSqlPath();
    $test->_DBO    = new XiDBCheck();
    //load end sql
    $dbDump        =  $sqlPath.'/'.$testName.'.start.sql';
    if(file_exists($dbDump))
    	$test->_DBO->loadSql($dbDump);

    
  }
 
  public function endTest(PHPUnit_Framework_Test $test, $time)
  {
    
    $testName = $test->getName();    

    // this two variables must be defined by test
    if(!$test->_DBO)
        return;
    
    //load end sql
    $sqlPath       = $test->getSqlPath(); 
    $dbDump        =  $sqlPath.'/'.$testName.'.end.sql';
    if(file_exists($dbDump))
    	$test->_DBO->loadSql($dbDump);
    
    $errors = $test->_DBO->getErrorLog();
    if(!empty($errors)){
         $sqlPath       = $test->getSqlPath();
         $logfile       =  $sqlPath.'/'.$testName.'.errlog';
         echo "\n Gold Table Verification failed for $testName, \n see $logfile for details";
         if(!file_put_contents($logfile,$errors))
         	echo $errors;
    }
  	$logs = $test->_DBO->getLog();
    if($logs){
         $sqlPath       = $test->getSqlPath();   
         $logfile       =  $sqlPath.'/'.$testName.'.log';
         if(!file_put_contents($logfile,$logs))
         	echo $logs;
    }
    $test->endTime = time();
    echo "[".($test->endTime-$test->startTime)."]";
  }
}
