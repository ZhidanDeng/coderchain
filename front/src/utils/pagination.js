/**
 * Pagination
 *
 * @description It's responsible for pagination's logic, without getting involved in dom.
 * @author Mask
 */
class Pagination {
  constructor(config = {}) {
    this.currentPage = 1
    this.contents = config.contents || []
    this.perPageRecord = config.perPageRecord || 6
    this.totalPages = Math.ceil(this.contents.length / this.perPageRecord)
  }

  getPageContent(page) {
    let recordPos = (page - 1) * this.perPageRecord
    this.currentPage = page
    return this.contents.slice(recordPos, recordPos + this.perPageRecord)
  }

  getPreviousPageContent() {
    let page
    if (this.currentPage < 2) {
      page = this.currentPage
    } else {
      this.currentPage--
      page = this.currentPage
    }
    return this.getPageContent(page)
  }

  getNextPageContent() {
    let page
    if (this.currentPage >= this.totalPages) {
      page = this.totalPages
    } else {
      this.currentPage++
      page = this.currentPage
    }
    return this.getPageContent(page)
  }
}

/**
 * PaginationWithDOM
 *
 * @description It's responsible for pagination's dom structure.
 * @author Mask
 */
class PaginationWithDOM extends Pagination {
  constructor(config, callback) {
    super(config)
    this.callback = callback
    this.pageBtnNum = config.pageBtnNum || 3
    // DOM Element
    this.pageContainerId = config.pageContainerId || 'mPageItem'
    this.pageBtnPrevText = config.pagePrevText || 'Prev'
    this.pageBtnNextText = config.pageNextText || 'Next'
    this.pageBtnClass = config.pageBtnClass || 'm-page-btn'
    this.pageBtnPrevClass = config.pageBtnPrevClass || 'm-page-btn-prev'
    this.pageBtnNextClass = config.pageBtnNextClass || 'm-page-btn-next'
    this.pageItemClass = config.pageItemClass || 'm-page-item-num'
    this.pageItemActiveClass = config.pageItemActiveClass || 'active'

    this._init()
  }

  _init() {
    this._buildUI()
    this._bindAction()

    let content = this.getPageContent(this.currentPage)
    // execute the customized function to render data
    this.callback.call(null, content)
  }

  _buildUI() {
    let start
    let end
    let html = ''
    let length = this.totalPages
    let middle = Math.floor(this.pageBtnNum / 2)
    let $page = document.querySelector(`#${this.pageContainerId}`)

    // html += `<span class="${this.pageBtnClass} ${this.pageBtnPrevClass}" data-page="prev">${this.pageBtnPrevText}</span>`

    if (this.pageBtnNum >= length) {
      // show all page number
      start = 1
      end = length
    } else if (this.currentPage - middle < 1) {
      start = 1
      end = (start + this.pageBtnNum - 1) % length
    } else if (this.currentPage + middle > length) {
      end = length
      start = (end - this.pageBtnNum + 1) % length
    } else {
      start = this.currentPage - middle
      end = this.currentPage + middle
    }

    if (this.currentPage !== 1 && this.totalPages > 1) {
    html += `<li>
        <span aria-hidden="true" aria-label="Previous" class="${
      this.pageBtnClass
      } ${this.pageBtnPrevClass}" data-page="prev">${
      this.pageBtnPrevText
      }</span>
    </li>`
    }

    if (start > 3) {
      html += `<li>
        <span class="${this.pageItemClass}
      }" data-page="1">1</span>
      </li><li>...</li>`
    }

    for (let i = start; i <= end; i++) {
      if (i === this.currentPage) {
        // html += `<span class="${this.pageItemClass} ${this.pageItemActiveClass}">${i}</span>`
        html += `<li>
        <span class="${this.pageItemClass} ${
          this.pageItemActiveClass
        }" data-page="${i}">${i}</span>
    </li>`
      } else {
        html += `<li>
        <span class="${this.pageItemClass}" data-page="${i}">${i}</span>
    </li>`
      }
    }
    // html += `<span class="${this.pageBtnClass} ${this.pageBtnNextClass}" data-page="next">${this.pageBtnNextText}</span>`

    // 18 ... 21
    if (end < this.totalPages - 2) {
      html += `<li>...</li><li>
        <span class="${this.pageItemClass}
      }" data-page="${this.totalPages}">${this.totalPages}</span>
      </li>`
    }

    if (this.currentPage !== this.totalPages && this.totalPages > 1) {
      html += `<li>
        <span aria-hidden="true" aria-label="Next" class="${
        this.pageBtnClass
        } ${this.pageBtnNextClass}" data-page="next">${
        this.pageBtnNextText
        }</span>
    </li>`
    }

    $page.innerHTML = html
  }

  _bindAction() {
    let $page = document.querySelector(`#${this.pageContainerId}`)
    // bind action
    $page.onclick = this._action.bind(this)

    // 这种方式会多次绑定上click事件，暂不启用，因为页面是同一个元素
    // $page.removeEventListener('click', this._action.bind(this))
    // $page.addEventListener('click', this._action.bind(this))
  }

  goPage(page) {
    this.getPageContent(parseInt(page))
    let content = this.getPageContent(this.currentPage)
    // render page structure
    this._buildUI()
    this.callback.call(null, content)
  }

  _action(e) {
    let target = e.target
    if (target.hasAttribute('data-page')) {
      let page = target.getAttribute('data-page')
      let needBuild = true
      switch (page) {
        case 'prev':
          if (this.currentPage === 1) {
            needBuild = false
          } else {
            this.getPreviousPageContent()
          }
          break
        case 'next':
          if (this.currentPage === this.totalPages) {
            needBuild = false
          } else {
            this.getNextPageContent()
          }
          break
        default:
          if (this.currentPage === parseInt(page)) {
            needBuild = false
          } else {
            this.getPageContent(parseInt(page))
          }
          break
      }

      console.log('needBuild: ', needBuild)
      let content = this.getPageContent(this.currentPage)
      if (needBuild) {
        // render page structure
        this._buildUI()
        this.callback.call(null, content)
      } else {
        // console.log('不触发渲染函数')
      }
    }
  }
}

export { Pagination, PaginationWithDOM }

