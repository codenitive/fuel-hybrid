/*
 * This is inspired by the CodeIgniter user guide's create_menu function.
 * http://codeigniter.com/user_guide/nav/nav.js
 *
 * It extracts the navigation to a single file for easier updating.
 */

//define document navigation
var nav = {
		"Basic": {
			"Home":					"index.html"
		},
		
		"Installation": {
			"Instructions": 		"installation/instructions.html",
			"Download": 			"installation/download.html"
		},

		"Classes": {
			"Acl": 					"classes/acl.html",
			"Curl": 				"classes/curl.html",
			//"Factory": 				"classes/factory.html",
			"Input": 				"classes/input.html",
			"Html": 				"classes/html.html",
			"Pagination": 			"classes/pagination.html",
			"Request": 				"classes/request.html",
			"Restserver": 			"classes/restserver.html",
			"Swiftmail": 			"classes/swiftmail.html",
			"Uri": 					"classes/uri.html",
			"View": 				"classes/view.html"
		},

		"Auth": {
			"Introduction": 		"auth/introduction.html",
			"Usage": 				"auth/usage.html",
			"Schema": 				"auth/schema.html",
			"Examples": {
				"User": 			"auth/user.html",
				"OAuth/OAuth2": 	"auth/oauth.html"
			}
		},
		/*
		"Chart" : {
			"Introduction": 		"chart/todo.html"
		},*/

		"Controller": {
			"Introduction": 		"controller/introduction.html",
			"Usage": {
				"Hybrid": 			"controller/todo.html",
				"Frontend": 		"controller/todo.html"
			},
			"Using Template": 		"controller/todo.html"
		},
		
		/*
		"Template": {
			"Introduction": 		"template/todo.html"
		}, */
		
		"Parser": {
			"Introduction": 		"parser/introduction.html" /*,
			"Driver types": {
				"Markdown": 		"parser/todo.html"
			}*/
		},

		"Refine": {}

};

//insert the navigation
function show_nav(page, path)
{
	active_path = window.location.pathname;
	path = path == null ? '' : path;
	$.each(nav, function(section,links) {
		
		var li = $('<li>')
			.addClass('dropdown')
			.attr('id', 'nav_'+section.toLowerCase().replace(' ', ''));
		var a  = $('<a href="#"/>').addClass('menu').html(section).appendTo(li);

		$('#topbar > ul').append(li);
		
		var ul = $('<ul>').addClass('dropdown-menu').appendTo(li);
		ul.append(generate_nav(path, links));
	});

	$('#topbar').dropdown();
}

//generate the navigation
function generate_nav(path, links)
{
	var html = '';
	$.each(links, function(title, href) {
		if (typeof(href) == "object")
		{
			for(var link in href) break;
			html = html + '<li><a href="'+path+href[link]+'">' + title + '</a>';
			html = html + '<ul>' + generate_nav(path, href) + '</ul></li>';
		}
		else
		{
			active = '';
			if (active_path.indexOf(href, active_path.length - href.length) != -1)
			{
				active = ' class="active"';
			}
			html = html + '<li><a href="'+path+href+'"'+active+'>'+title+'</a></li>';
		}
	});
	return html;
}

// IE8 fix for displaying the sections correctly.
var dummy_section = document.createElement('section');
