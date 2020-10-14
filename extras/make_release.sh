if [ -e ../html/xoops.css ]; then
	find .. -type d | grep '/CVS' | xargs rm -rf
	find .. -type d | grep '/.xml' | xargs rm -rf
	find .. -type d | grep '/.doxy' | xargs rm -rf
	mv ../html/modules/system ../extras/system
	rm ../.project
fi 
