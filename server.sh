#!/bin/sh
pidFile="/var/qserver_pid";
function start(){
	php queue_server.php $pidFile
  
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


case $1 in 
	
	start )
		start
	;;
	stop)
		stop
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

