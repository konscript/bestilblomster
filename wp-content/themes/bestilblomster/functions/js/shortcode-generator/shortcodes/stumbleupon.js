wooShortcodeMeta={
	attributes:[
		{
			label:"Optional URL to Stumble",
			id:"url",
			help:"Optionally place the URL you want viewers to 'Stumble' here. Defaults to the current URL being viewed."
		},
		{
			label:"Design",
			id:"design",
			help:"Choose a design for your Stumbleupon badge.",
			controlType:"select-control", 
			selectValues:['horizontal_large', 'horizontal_medium', 'horizontal_small', 'icon_small', 'icon_large', 'vertical_count'],
			defaultValue: 'horizontal_large', 
			defaultText: 'horizontal_large (Default)'
		}, 
		{
			label:"Float",
			id:"float",
			help:"Float left, right, or none.",
			controlType:"select-control", 
			selectValues:['', 'left', 'right'],
			defaultValue: '', 
			defaultText: 'none (Default)'
		}, 
		{
			label:"Use Current Post",
			id:"use_post",
			help:"Optionally use the URL to the current post (best used when including this shortcode on each post in an archive or search screen).",
			controlType:"select-control", 
			selectValues:['', 'true'],
			defaultValue: '', 
			defaultText: 'false (Default)'
		}
		],
		defaultContent:"",
		shortcode:"stumbleupon"
};