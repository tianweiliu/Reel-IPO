Reel-IPO
========

Reel IPO is a stock widget that can retrieve real-time update from Purdue University CGT 411/450 IPO page.

Sample JSONP call
=================
`````PHP
<blockquote>
<div id="stock-widget"><div class="ajax-loader-white">Loading...</div></div>
<div class="stock-ad">Powered by <a href="http://reelinteraction.com/reel-ipo/" target="_blank">Reel IPO Plus</a></div>
<script type="text/javascript" src="http://www.omnipotent.net/jquery.sparkline/2.1/jquery.sparkline.js"></script>
<script type="text/javascript">
  jQuery.ajax({                                                                                                                                                                                                        
    type: 'GET',                                                                                                                                                                                                 
    url: 'http://reelinteraction.com/stock/',
	data: {group_id: "X3", semester:"f12", key:"28b6db7f6c62a9edeca47384a3e891a7"},                                                                                                                                              
    dataType: 'jsonp',                                                                                                                                                                                                
    success: function(json) { jQuery("#stock-widget").html("<div class=\"stock-ticker\">"+json.name+"</div><div class=\"stock-chart\"></div><div class=\"stock-price\">"+json.value+"</div><div class=\"stock-change\">+"+json.change+"</div><div class=\"stock-buy\"><a href='mailto:nate@reelinteraction.com'>Buy Stock</a></div><div class=\"stock-shares\">Shares Available: "+json.available+"</div><div class=\"stock-date\">Last Updated: "+json.date+"</div>");
				if (json.history) {
					var x = [];
					var y = [];
					for (value in json.history) {
						x.push(value);
						y.push(json.history[value]);
					}
					jQuery(".stock-chart").sparkline(y, {xvalues: x, type: 'line', lineColor: "#eee", fillColor:"#555", defaultPixelsPerValue: 5, tooltipPrefix: "$"});
				} },
	error: function() { jQuery("#stock-widget").html("Cannot retrieve stock updates."); },
    jsonp: 'jsonp'                                                                                                                                                
});
</script>
</blockquote>
