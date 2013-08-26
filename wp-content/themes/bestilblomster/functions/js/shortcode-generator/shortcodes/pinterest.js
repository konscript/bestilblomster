wooShortcodeMeta={
	attributes:[
		{
			label:"Optional URL to Pin",
			id:"url",
			help:"Optionally place the URL you want viewers to 'Pin' here. Defaults to the current URL being viewed."
		},
		{
			label:"Optional image URL to Pin",
			id:"image_url",
			help:"Optionally place the image URL you want viewers to 'Pin' here. Defaults to the featured image for the current entry."
		},
		{
			label:"Count Position",
			id:"count",
			help:"Choose a counter display style for your Pinterest button.",
			controlType:"select-control", 
			selectValues:['horizontal', 'vertical', 'none'],
			defaultValue: 'horizontal', 
			defaultText: 'horizontal (Default)'
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
		shortcode:"pinterest"
};