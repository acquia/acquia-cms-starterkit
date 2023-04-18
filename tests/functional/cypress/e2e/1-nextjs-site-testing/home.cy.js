describe('homepage', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('http://127.0.0.1:3000/')
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

  it('verify page highlight section', () => {

    // We can go even further and check that the default todos each contain
    // the correct text. We use the `first` and `last` functions
    // to get just the first and last matched elements individually,
    // and then perform an assertion with `should`.
    cy.get('section h2').should('have.text', 'Powered by Acquia CMS')
    cy.get('section h1').should('have.text', 'A headless Next.js site')
    cy.get("section p").first().contains("This is placeholder text")
    cy.get("section").find('img').should('have.attr', 'alt', 'Logo')
  })

  it('verify page main container', () => {
    const articles = [
      {
        "title": "Event two medium length placeholder heading."
      },
      {
        "title": "Event five medium length placeholder heading."
      },
      {
        "title": "Event three medium length placeholder heading."
      }
    ];
    cy.get("main div.container > h2").first().should("have.text", "Featured Events")
    const element = cy.get("main div.container [data-cy='featured-events']").find("article")
    element.should("have.length", 3)
    element.each(($el, index, $list) => {
      cy.wrap($el).find("h2").should("have.text", articles[index].title)
      cy.wrap($el).find("a").should("have.attr", "href").then(href => {
        expect(href.startsWith("/event/webinar")).to.be.true
      });
      cy.wrap($el).find("span")
    });
    cy.get("main div:nth-of-type(3) h2").first().should("have.text", "Contact Us")
    cy.get("main div.container [data-cy='contact-us']").find("article").should("have.length", 3)
  })

  it('verify page footer', () => {
    cy.get('footer nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

})
