describe('articles', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('http://127.0.0.1:3000/articles')
  })

  const links = [
    "Home",
    "Articles",
    "Events",
    "People",
    "Places",
  ]

  it('verify page header', () => {

    // We can go even further and check that the default todos each contain
    // the correct text. We use the `first` and `last` functions
    // to get just the first and last matched elements individually,
    // and then perform an assertion with `should`.
    cy.get('header span').should('have.text', 'Acquia CMS')
    cy.get("header").find('img').should('have.attr', 'alt', 'Logo')
      .should('have.attr', 'loading', 'lazy')
    cy.get('header nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

  it('verify article header section', () => {
    cy.get('div h1.leading-tight').should('have.text', 'Articles')
    cy.get("div p.text-gray-600").should('have.text', 'List of latest articles.')
  })


  it('verify articles list', () => {
    const description = "This is placeholder text. If you are reading this, it is here by mistake and we would appreciate it if you could email us with a link to the page you found it on. This is placeholder text. If you are reading this, it is here by mistake and we would appreciate it if you could email us with a link to the page you found it on.";
    const articles = [
      {
        "title": "Article ten medium length placeholder heading.",
        "link": "/article/blog/article-ten-medium-length-placeholder-heading",
        "author": "Clare Harris"
      },
      {
        "title": "Article eleven medium length placeholder heading.",
        "link": "/article/blog/article-eleven-medium-length-placeholder-heading",
        "author": "Anaisha Agarwal"
      },
      {
        "title": "Article eight medium length placeholder heading.",
        "link": "/article/news/article-eight-medium-length-placeholder-heading-0",
        "author": "Clare Harris"
      },
      {
        "title": "Article six medium length placeholder heading.",
        "link": "/article/blog/article-six-medium-length-placeholder-heading",
        "author": "Sarah Jones"
      },
      {
        "title": "Article thirteen medium length placeholder heading.",
        "link": "/article/press-release/article-thirteen-medium-length-placeholder-heading",
        "author": "Daniel William-Blake"
      },
      {
        "title": "Article three medium length placeholder heading.",
        "link": "/article/blog/article-three-medium-length-placeholder-heading",
        "author": "James Anderson"
      },
      {
        "title": "Article eight medium length placeholder heading.",
        "link": "/article/blog/article-eight-medium-length-placeholder-heading",
        "author": "Alex Kowen"
      },
      {
        "title": "Article two medium length placeholder heading.",
        "link": "/article/blog/article-two-medium-length-placeholder-heading",
        "author": "James Anderson"
      },
      {
        "title": "Article fourteen medium length placeholder heading.",
        "link": "/article/blog/article-fourteen-medium-length-placeholder-heading",
        "author": "Daniel William-Blake"
      },
      {
        "title": "Article five medium length placeholder heading.",
        "link": "/article/blog/article-five-medium-length-placeholder-heading",
        "author": "Peter Withers"
      },
      {
        "title": "Article eight medium length placeholder heading.",
        "link": "/article/news/article-eight-medium-length-placeholder-heading",
        "author": "Clare Harris"
      },
      {
        "title": "Article twelve medium length placeholder heading.",
        "link": "/article/blog/article-twelve-medium-length-placeholder-heading",
        "author": "Daniel William-Blake"
      },
      {
        "title": "Article nine medium length placeholder heading.",
        "link": "/article/blog/article-nine-medium-length-placeholder-heading",
        "author": "Clare Harris"
      },
      {
        "title": "Article four medium length placeholder heading.",
        "link": "/article/blog/article-four-medium-length-placeholder-heading",
        "author": "Peter Withers"
      },
      {
        "title": "Article seven medium length placeholder heading.",
        "link": "/article/news/article-seven-medium-length-placeholder-heading",
        "author": "Alex Kowen"
      },
      {
        "title": "Article one long length wrapping placeholder heading.",
        "link": "/article/news/article-one-long-length-wrapping-placeholder-heading",
        "author": "James Anderson"
      },
    ];
    const element = cy.get("main div.container").find("article")
    element.should("have.length", articles.length)
    element.each(($el, index, $list) => {
      // Top section of article list.
      cy.wrap($el).find("a").should("have.attr", "href",  articles[index].link)
      cy.wrap($el).get("a.block div.image__wrapper").find("img").should("have.attr", "alt", "Image placeholder")
      // Bottom section of article list.
      cy.wrap($el).find("span.font-semibold").should("have.text", articles[index].author);
      cy.wrap($el).find("a").should("have.attr", "href",  articles[index].link)
      cy.wrap($el).find("h2").should("have.text",  articles[index].title)
      if (articles[index].title === 'Article one long length wrapping placeholder heading.') {
        const description = 'This is placeholder text. If you are reading this, it is here by mistake and we would appreciate it if you could email us with a link to the page you found it on.'
        cy.wrap($el).find("[data-cy='summary']").should("have.text", description)
      }
      else {
        cy.wrap($el).find("[data-cy='summary']").should("have.text", description)
      }

    });
  })

  it('verify page footer', () => {
    cy.get('footer nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

})
