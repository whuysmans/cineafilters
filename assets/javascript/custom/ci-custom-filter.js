;(function ($) {
  // this is the object that holds the filter state
  var activeFilters = {}

  String.prototype.ciptEscapeHTML = function () {
    return this.replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;')
  }

  function ciptIsNumeric (n) {
    return !isNaN(parseFloat(n)) && isFinite(n)
  }

  // ajax-get the filters from the php
  function showFilters () {
    $.ajax({
      method: 'POST',
      url: custom_filter.ajax_url,
      action: 'get_filters',
      data: {
        'action': 'get_filters'
      },
      dataType: 'json',
      success: function (msg) {
        renderFilters(msg)
        handleIncomingUrlParams()
      }
    })
  }

  function clearFilter () {
    activeFilters = {}
  }

  // create the domelements that represent the filters
  function renderFilters (msg) {
    var filterList = document.createElement('ul')

    for (var item in msg) {
      var filterGroup = msg[item]
      var taxGroup = document.createElement('li')
      var taxTerms = document.createElement('ul')
      taxGroup.setAttribute('is-tax', filterGroup['meta']['is_tax'])
      taxGroup.setAttribute('is-cpt', filterGroup['meta']['is_cpt'])
      // taxGroup.appendChild(document.createTextNode(filterGroup['meta']['name']))
      taxGroup.innerHTML = filterGroup['meta']['name']
      taxGroup.appendChild(taxTerms)

      filterGroup['filters'].forEach(function (term) {
        var li = document.createElement('li')
        li.appendChild(document.createTextNode(term.name.ciptEscapeHTML()))
        li.classList.add('ci-filter')
        li.setAttribute('active', 'false')
        li.setAttribute('filter-group', filterGroup['meta']['slug'])
        li.setAttribute('slug', term.slug)
        // set ids for event types, needed for the query
        if (term.hasOwnProperty('id') && ciptIsNumeric(term.id)) {
          li.setAttribute('filter-id', term.id)
        }
        else {
          li.setAttribute('filter-id', term.term_id)
        }
        li.addEventListener('click', handleFilterClick, false)
        taxTerms.appendChild(li)
      })
      filterList.appendChild(taxGroup)
    }
    var main = document.querySelector('.filterlist')
    var parent = main.parentNode
    parent.insertBefore(filterList, main)

    // search part
    buildSearch()
  }

  // search field logic for both filters and custom search terms
  function buildSearch (data) {
    var input = document.querySelector('input[type=search]')
    var items = document.querySelectorAll('.ci-filter')
    var data = new Array()
    items.forEach(function (item) {
      data.push(item.textContent)
    })
    // include autofill library
    var aws = new Awesomplete(input)
    aws.list = data
    // case 1: catch enter on inputfield without click on suggestion => bypass awesomplete
    input.addEventListener('keyup', function (e) {
      if (e.keyCode === 13) {
        var nodeItems = Array.prototype.filter.call(items, function (el) {
          return el.innerText.toLowerCase() === e.target.value.toLowerCase()
        })
        if (nodeItems.length !== 0) {
          var node = nodeItems[0]
          // trigger change, with check forceFilterOn (don't deactivate if filter is already active)
          updateFilters(node, true)
          handleFilterChange()
        }
      }
    })
    // case 2: regular awesomplete logic on suggestion clicked
    window.addEventListener('awesomplete-selectcomplete', function (e) {
      var nodeItems = Array.prototype.filter.call(items, function (el) {
        return el.innerText === e.text.value
      })
      var node = nodeItems[0]
      // trigger change, with check forceFilterOn (don't deactivate if filter is already active)
      updateFilters(node, true)
      handleFilterChange()
      input.value = ''
    })
  }

  function isSearch () {
    var str = window.location.search.substr(1)
    return str.indexOf('s') === 0 && str.indexOf('=') === 1
  }

  // helper function to parse filter get params
  function getParams () {
    var paramStr = window.location.search.substr(1)
    return paramStr != null && paramStr != '' ? makeArray(paramStr) : []
  }

  // helper helper function to parse filter get params
  function makeArray (str) {
    var params = new Array()
    var paramArr = str.split('&')
    paramArr.map(function (substr) {
      var tempArr = substr.split('=')
      var key = tempArr[0].replace('cinea_', '')
      var value = tempArr[1]
      var myObj = new Object()
      myObj.key = key
      myObj.value = value
      params.push(myObj)
    })
    return params
  }

  // this function handles direct links to filtered views
  function handleIncomingUrlParams () {
    // important: do not trigger if this is a search request!
    if (isSearch()) {
      activeFilters = {}
      return
    }
    var params = getParams()
    // no filter params => no action
    if (params.length === 0)
      return
    // trigger clicks on each of the filter params in order to filter the results
    params.map(function (paramObj) {
      var node = document.querySelector('li[filter-group=' + paramObj.key + '][slug=' + paramObj.value + ']')
      updateFilters(node)
    })
    // trigger query when we've updated all filters & the filter state object
    handleFilterChange()
  }

  // update filter list & manage filter state object
  function updateFilters (node, forceFilterOn = false) {
    var filterGroup = node.getAttribute('filter-group')
    var slug = node.getAttribute('slug') // node.textContent.toLowerCase()
    var id = node.getAttribute('filter-id') // node.textContent.toLowerCase()

    // via search: first check if filter is already on
    if (forceFilterOn && node.getAttribute('active') === 'true') {
      return
    }

    // set inactive flow
    if (node.getAttribute('active') === 'true') {
      node.setAttribute('active', 'false')
      $(node).css('color', 'black')
      // remove from filter object
      var index = activeFilters[ filterGroup ].map(function (elem) {
        return elem.slug
      }).indexOf(slug)
      activeFilters[ filterGroup ].splice(index, 1)
      // remove empty nodes from filter object
      if (activeFilters[ filterGroup ].length === 0)
        delete activeFilters[ filterGroup ]

    // set active flow
    } else if (node.getAttribute('active') === 'false') {
      node.setAttribute('active', 'true')
      $(node).css('color', 'green')
      // we need objects with multiple props
      var termObj = {
        'slug': slug,
        'id': id
      }
      // if node does not exist, create it as array
      if (!(activeFilters.hasOwnProperty(filterGroup))) {
        activeFilters[ filterGroup ] = [ termObj ]
      // if it exists, push to array
      } else {
        activeFilters[ filterGroup ].push(termObj)
      }
    }
  }

  // handle click on filter, update filter state object & trigger query
  function handleFilterClick (e) {
    updateFilters(e.target)
    handleFilterChange()
  }

  // call helper function for url update and do the query
  function handleFilterChange () {
    createFakeSearchString()
    $.ajax({
      method: 'POST',
      url: custom_filter.ajax_url,
      data: {
        action: 'get_results',
        data: activeFilters
      },
      success: function (result) {
        filter(result)
      },
      error: function (err) {
        // handleError(err)
      }
    })
  }

  function handleError (msg) {
    alert(msg)
  }

  // update url query string so that it reflects the latest filter selection
  function createFakeSearchString () {
    var queryString = '?'
    for (var item in activeFilters) {
      // filter out inherited obj props
      if (!activeFilters.hasOwnProperty(item))
        continue
      // get the terms and add them to the query string
      activeFilters[ item ].forEach(function (term) {
        // var str = term.slug.replace(/\s/g, '')
        queryString += 'cinea_' + item + '=' + term.slug + '&'
      })
    }
    // remove trailing ampersand
    queryString = queryString.substring(0, queryString.length - 1)
    // push it
    if (queryString === '')
      queryString = clearUrl(window.location.href)
    if (window.history.pushState) {
      window.history.pushState(null, 'filter', queryString)
    }
  }

  function clearUrl (url) {
    var cleanUrl = url.split('?')
    return cleanUrl[0]
  }

  // show the query result on the page
  function filter (result) {
    var mainNode = document.querySelector('#article-list')
    while (mainNode.firstChild) {
      mainNode.removeChild(mainNode.firstChild)
    }
    $(mainNode).prepend(result)
    setTimeout(function () {
      updateCount()
    }, 0)
  }

  function updateCount () {
    var count = document.querySelectorAll('.article-preview').length
    var countEl = document.querySelector('.result-count')
    var articleStr = count === 1 ? 'ARTIKEL' : 'ARTIKELS'
    countEl.innerText = count + ' ' + articleStr + ' in Magazine'
  }

  // initializing function
  showFilters()
})(jQuery)
