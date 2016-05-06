#!/bin/bash
pidFile="/var/qserver_pid";
pidDbFile="/var/db_server_pid";
function start(){
	php ./src/Core/YcfHttpServer.php $pidFile;
  
	printf $?
	if [ $? == 0 ]; then
		printf "\qserver start OK\r\n"
		return 0
	else
		printf "\qserver start FAIL\r\n"
		return 1
	fi
}

function stop(){

	$(ps aux  | grep "$pidFile" |grep -v "grep "| awk '{print $2}'    | xargs  kill -9)    
	

	PROCESS_NUM2=$(ps aux  | grep "$pidFile" |grep -v "grep "| awk '{print $2}'   | wc -l )    
	if [ $PROCESS_NUM2 == 0 ]; then
		printf "\qserver stop OK\r\n"
		return 0
	else
		printf "\qserver stop FAIL\r\n"
		return 1
	fi
	
}


function startDB(){
	php ./src/Core/YcfDBServer.php $pidDbFile
  
	if [ $? == 0 ]; then
		printf "\db_server start OK\r\n"
		return 0
	else
		printf "\db_server start FAIL\r\n"
		return 1
	fi
}

function stopDB(){

	$(ps aux  | grep "$pidDbFile" |grep -v "grep "| awk '{print $2}'    | xargs  kill -9)    
	

	PROCESS_NUM2=$(ps aux  | grep "$pidDbFile" |grep -v "grep "| awk '{print $2}'   | wc -l )    
	if [ $PROCESS_NUM2 == 0 ]; then
		printf "\db_server stop OK\r\n"
		return 0
	else
		printf "\db_server stop FAIL\r\n"
		return 1
	fi
	
}


case $1 in 
	
	start )
		start
	;;
	startDB )
		startDB
	;;

	stop)
		stop
	;;
	stopDB)
		stopDB
	;;
	restart)
		stop
		sleep 1
		start
	;;

	*)
		start
	;;
esac

