
$( document ).ready(function(){

	$(".mayalist").each(function(){
		var obj_name=$(this).attr("id");
	    obj_name=new MayaList(obj_name);
	    $(this).find(".sort-selector").change(function(){obj_name.Sort();});
	    $(this).find("li").each(function(index){
	    	$(this).find(".btn").click(function(){obj_name.ShowPage(index+1);});
	    });
	});
});



var MayaList = function(objectid, pageqty)
 {
	var obj=$("#"+objectid);
	var pageqty=obj.data("pageqty");
	var thispage;
	var thispage=1;
	var pagecount=Math.ceil(parseInt(obj.find(".list-obj").length)/pageqty);

	this.UpdateNav=function()
	{
		var nav=$(obj).find(".pagination-nav");
		nav.append("<ul class='pagination'></ul>");
		 for (var i = 1; i <= pagecount; i++)
		  {
			 var thisclass="";
			 if(i==1){ thisclass="active";}
		   $(obj).find(".pagination").append("<li class='"+thisclass+"'><button class='btn-primary btn'>"+i+" <span class='sr-only'>(current)</span></button></li>");
		  }
	}

	 this.UpdateDivs=function()
	 {
		 var i=0;
		 var p=0;
		 for(k=0; k<=pagecount;k++){ $(".list-obj").removeClass("page-"+k);}

		 $(".list-obj").each(function(){
			if((i % pageqty)==0) p++;
			i++;
			$(this).addClass("page-"+p);
		 });
		 $(".list-obj").hide();
		 $(".page-1").show();
	 }

	 this.ShowPage=function(page)
	  {
		 if(page!=this.thispage)
			 {
			 thispage=page;
			 obj.find("li").removeClass("active");
             obj.find("li:nth-child("+thispage+")").addClass("active");
		     $(".list-obj").hide("slow");
		     $(".page-"+page).show("slow");
			 }
	  }

	 this.Sort=function()
	  {
		 var selectval=obj.find(".sort-selector").val();
		 var arr=selectval.split("-");
		 var data_attribute=arr[0];
		 var order=arr[1];
		 var selector=obj.find('.list-obj').sort(function (a, b) {
		      var contentA =( $(a).data(data_attribute));
		      var contentB =( $(b).data(data_attribute));
		      if(order=="asc")
		    	  {
		           return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;
		    	  }
		      else
		    	  {
		    	   return (contentA > contentB) ? -1 : (contentA < contentB) ? 1 : 0;
		    	  }
		   });
		obj.append(selector);
		this.UpdateDivs();
		this.ShowPage(1);
	  }
	 this.UpdateNav();
	 this.UpdateDivs();
 }
