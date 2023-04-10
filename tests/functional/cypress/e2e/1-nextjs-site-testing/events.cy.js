/// <reference types="cypress" />

// Welcome to Cypress!
//
// This spec file contains a variety of sample tests
// for a todo list app that are designed to demonstrate
// the power of writing tests in Cypress.
//
// To learn more about how Cypress works and
// what makes it such an awesome testing tool,
// please read our getting started guide:
// https://on.cypress.io/introduction-to-cypress

describe('events', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('http://localhost:3000/events')
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

  it('verify events header section', () => {
    cy.get('div h1.leading-tight').should('have.text', 'Events')
    cy.get("div p.text-gray-600").should('have.text', 'Upcoming Events.')
  })


  it('verify events list', () => {
    const description = "This is placeholder text. If you are reading this, it is here by mistake and we would appreciate it if you could email us with a link to the page you found it on. This is placeholder text. If you are reading this, it is here by mistake and we would appreciate it if you could email us with a link to the page you found it on.";
    const events = [
      {
        "title": "Event two medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-two-medium-length-placeholder-heading"
      },
      {
        "title": "Event five medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-five-medium-length-placeholder-heading"
      },
      {
        "title": "Event three medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-three-medium-length-placeholder-heading"
      },
      {
        "title": "Event four medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-four-medium-length-placeholder-heading"
      },
      {
        "title": "Event six medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-six-medium-length-placeholder-heading"
      },
      {
        "title": "Event one medium length placeholder heading.",
        "link": "/event/webinar/2023/05/event-one-medium-length-placeholder-heading"
      },
      {
        "title": "Event one medium length placeholder heading.",
        "link": "/event/webinar/2025/01/event-one-medium-length-placeholder-heading"
      },
      {
        "title": "Event two medium length placeholder heading.",
        "link": "/event/conference/2025/01/event-two-medium-length-placeholder-heading"
      },
      {
        "title": "Event three medium length placeholder heading.",
        "link": "/event/conference/2025/02/event-three-medium-length-placeholder-heading"
      },
      {
        "title": "Event four medium length placeholder heading.",
        "link": "/event/webinar/2025/03/event-four-medium-length-placeholder-heading"
      },
      {
        "title": "Event five medium length placeholder heading.",
        "link": "/event/meet/2025/04/event-five-medium-length-placeholder-heading"
      },
      {
        "title": "Event six medium length placeholder heading.",
        "link": "/event/webinar/2025/05/event-six-medium-length-placeholder-heading"
      },
      {
        "title": "Event seven short placeholder heading.",
        "link": "/event/webinar/2025/06/event-seven-short-placeholder-heading"
      },
      {
        "title": "Event eight short placeholder heading",
        "link": "/event/workshop/2025/07/event-eight-short-placeholder-heading"
      },
      {
        "title": "Event nine short placeholder heading.",
        "link": "/event/webinar/2025/08/event-nine-short-placeholder-heading"
      },
      {
        "title": "Event eleven short placeholder heading.",
        "link": "/event/conference/2025/09/event-eleven-short-placeholder-heading"
      },
      {
        "title": "Event ten short placeholder heading.",
        "link": "/event/conference/2025/09/event-ten-short-placeholder-heading"
      },
      {
        "title": "Event twelve long length placeholder heading.",
        "link": "/event/meet/2025/10/event-twelve-long-length-placeholder-heading"
      },
      {
        "title": "Event thirteen medium length placeholder heading.",
        "link": "/event/webinar/2025/11/event-thirteen-medium-length-placeholder-heading"
      },
      {
        "title": "Event fourteen medium length placeholder heading.",
        "link": "/event/workshop/2025/12/event-fourteen-medium-length-placeholder-heading"
      }
    ];
    const element = cy.get("main div.container").find("article")
    element.should("have.length", events.length)
    element.each(($el, index, $list) => {
      // Left section of people list.
      cy.wrap($el).find("a").should("have.attr", "href",  events[index].link)
      cy.wrap($el).get("a.block div.image__wrapper").find("img").should("have.attr", "alt", "Image placeholder")
      // Right section of people list.
      cy.wrap($el).find("a").should("have.attr", "href",  events[index].link)
      cy.wrap($el).find("h2").should("have.text",  events[index].title)
      cy.wrap($el).find("p").should("have.text", description)
    });
  })

  it('verify page footer', () => {
    cy.get('footer nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

})
