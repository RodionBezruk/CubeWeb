function plugin_file_details(){
	this.name='file_details',
	this.title='file details'; 
	this.mode=2;
	this.writable=2;
	this.category='returning'; 
	this.extensions=['all'];
	this.doFunction=function(files){
		var table=kfm_buildFileDetailsTable(File_getInstance(files[0]));
		kfm_modal_open(table,'File Details',[]); 
	}
}
kfm_addHook(new plugin_file_details());
