wooShortcodeMeta={
	attributes:[
		{
			label:"Twitter Username",
			id:"username",
			help:"Place your twitter username here. This would be http://twitter.com/<strong>woothemes</strong>."
		}, 
		{
			label:"Include Counter",
			id:"count",
			help:"Show your follower count.",
			controlType:"select-control", 
			selectValues:['false', ''],
			defaultValue: '', 
			defaultText: 'true (Default)'
		}, 
		{
			label:"Language",
			id:"language",
			help:"Select the language in which you want to display the button.",
			controlType:"select-control", 
			selectValues:['en', 'fr', 'de', 'it', 'es', 'ko', 'ja'],
			defaultValue: '', 
			defaultText: 'en (Default)'
		}, 
		{
			label:"Width",
			id:"width",
			help:"An optional width, in percentage (<strong>50%</strong) or pixel (<strong>50px</strong>) format."
		},
		{
			label:"Align",
			id:"align",
			help:"Used in conjunction with 'width' to align the button within the shortcode container DIV tag.",
			controlType:"select-control", 
			selectValues:['', 'left', 'right'],
			defaultValue: '', 
			defaultText: 'none (Default)'
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
			label:"Size",
			id:"size",
			help:"Specify the size of the button (medium or large)",
			controlType:"select-control", 
			selectValues:['', 'large'],
			defaultValue: '', 
			defaultText: 'medium (Default)'
		}, 
		{
			label:"Show Screen Name",
			id:"show_screen_name",
			help:"Optionally hide the display of your screen name on the button.",
			controlType:"select-control", 
			selectValues:['', 'false'],
			defaultValue: '', 
			defaultText: 'true (Default)'
		}
		],
		defaultContent:"",
		shortcode:"twitter_follow"
};