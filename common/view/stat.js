var DT = DT || {};
(function(){
	DT.showTM = function(tm){
		var dd = new Date(tm * 1000);
		//return dd.toGMTString();
		return dd.getFullYear() +
			'-' + (dd.getMonth() + 1) +
			'-' + dd.getDate() + 
			' ' + dd.getHours() + 
			':' + dd.getMinutes() + 
			':' + dd.getSeconds() 
	}
	function dataTable(doc,data){
		var cols  =  data.cols;
		var ds  = data.data;

		var html = '<table width="100%" > <thead>';
		html += '<tr>'
		var c;
		cols.forEach(function(v,k){
			html += "<th>" + v[1]+ "</th>";
		});
		html += '</tr>'
		html += '<tbody>'
		var vv = '',i,d;
		//ds.forEach( function(d){
		for(i in ds){
			d = ds[i];
			html += '<tr>'
			cols.forEach(function(v,key){
				vv = d && d[v[0]] || '0'
				html += "<td>" + vv + "</td>";
			})
			html += '</tr>'
		}
		html += '</tbody>'
		html += '</table>'
		doc.html(html);
		return doc.find('table');

	}   

	DT.view = function(jqid,data){
		var table = dataTable($(jqid),data);
		table.dataTable( { 
			"sDom": 'T<"clear">lfrtip'
			,"oTableTools": {
				"aButtons": [
					"copy",
					"print",
					{
						"sExtends": "csv",
						"sButtonText": "CSV"
					},
					{
						"sExtends": "xls",
						"sButtonText": "Excel"
					}

				],
				"sSwfPath": CURLP + "/jqtable/swf/copy_cvs_xls.swf"
			},
			"bPaginate": false
			,"bFilter": true
			,"bLengthChange": false
		}
	  );
	}

	DT.init = function(){
		$('.dt-table').dataTable( { 
			sDom: 'T<"clear">lfrtip',
			oTableTools: {
				"aButtons": [
					"copy",
					"print",
					{
						"sExtends": "csv",
						"sButtonText": "CSV"
					},
					{
						"sExtends": "xls",
						"sButtonText": "Excel"
					}

				],
				sSwfPath: CURLP + "/jqtable/swf/copy_cvs_xls.swf"
			}
			,bPaginate: false
			,bFilter: true  //搜索过滤
			,bInfo: false
			,bLengthChange: false
		});
	}


	DT.showScalarLine = function(id,tconf,data){
		var cols = tconf.cols;
		showdatas = []//;

	}




})();
