wooShortcodeMeta={
	attributes:[
		{
			label:"URL",
			id:"url",
			help:"Optional. Specify URL directly."
		},
		{
			label:"Style",
			id:"style",
			help:"Show your follower count.",
			controlType:"select-control", 
			selectValues:['horizontal', 'none', 'vertical'],
			defaultValue: '', 
			defaultText: 'horizontal (Default)'
		}, 
		{
			label:"Via",
			id:"source",
			help:"Optional. Username to mention in tweet."
		},
		{
			label:"Recommend",
			id:"related",
			help:"Optional. Related account to reference."
		},
		{
			label:"Hashtag",
			id:"hashtag",
			help:"Optional. Include a related hashtag."
		},
		{
			label:"Text",
			id:"text",
			help:"Optional tweet text (default: title of page)."
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
			label:"Float",
			id:"float",
			help:"Values: none, left, right (default: left).",
			controlType:"select-control", 
			selectValues:['', 'left', 'right'],
			defaultValue: 'left', 
			defaultText: 'left (Default)'
		},
		{
			label:"Lang",
			id:"lang",
			help:"Values: fr, de, es, js (default: english).", 
			controlType:"select-control", 
			selectValues:['', 'fr', 'de', 'es', 'js', 'hi', 'zh-cn', 'pt', 'id', 'hu', 'it', 'da', 'tr', 'fil', 'ko', 'sv', 'no', 'zh-tw', 'nl', 'ru', 'ja', 'fi', 'msa', 'pl'],
			defaultValue: '', 
			defaultText: 'english (Default)'
		},
		{
			label:"Use Post URL",
			id:"use_post_url",
			help:"Values: true, false (default: false).",
			controlType:"select-control", 
			selectValues:['', 'true'],
			defaultValue: '', 
			defaultText: 'false (Default)'
		}
		],
		defaultContent:"",
		shortcode:"twitter"
};