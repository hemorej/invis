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
	tags:
		label: Tags
		type: text
		help: Your Tags (Tag One, Tag Two, Tag 3).
		required: false
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