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

describe('places', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('http://localhost:3000/places')
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

  it('verify places header section', () => {
    cy.get('div h1.leading-tight').should('have.text', 'Places')
    cy.get("div p.text-gray-600").should('have.text', 'Our Offices')
  })


  it('verify places list', () => {
    const places = [
      {
        "title": "Boston Head Office",
        "address": "53 State Street, 10th FloorBoston, MA 02109",
        "contact": "1234 678 9101",
				"link": "/place/office/boston-head-office"
      },
      {
        "title": "Brighton Office",
        "address": "100-101 Queens RoadBrighton,  BN1 3XF",
        "contact": "1023 567 892",
				"link": "/place/office/brighton-office"
      },
      {
        "title": "London sales and support office",
        "address": "37 Cremer StreetLondon,  E2 8HD",
        "contact": "1234 678 9101",
				"link": "/place/office/london-sales-and-support-office"
      }
    ];
    const element = cy.get("main div.container div.gap-14").find("article")
    element.should("have.length", places.length)
    element.each(($el, index, $list) => {
      // Left section of place list.
      cy.wrap($el).find("a").should("have.attr", "href", places[index].link)
      cy.wrap($el).get("a.block div.image__wrapper").find("img").should("have.attr", "alt", "Image placeholder")
      // Right section of place list.
      cy.wrap($el).find("a").should("have.attr", "href", places[index].link)
      cy.wrap($el).find("h2").should("have.text", places[index].title)
      cy.wrap($el).find("div.space-y-4 div").should("have.text", places[index].address)
      cy.wrap($el).find("p").should("have.text", places[index].contact)
    });
  })

  it('verify page footer', () => {
    cy.get('footer nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

})
