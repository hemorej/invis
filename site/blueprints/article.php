title: Article
pages: false
files: true
fields:
	title:
		label: Title
		type: text
		help: The title of your article.
		required: true    
	published:
		label: Published
		type: text
		help: Publishing date (01 January 2012).
		required: true
	text:
		label: Your content
		type: textarea
		size: large
		help: Your content.
		buttons: 
			- bold
			- italic
			- email
			- link