describe('people', () => {
  beforeEach(() => {
    // Cypress starts out with a blank slate for each test
    // so we must tell it to visit our website with the `cy.visit()` command.
    // Since we want to visit the same URL at the start of all our tests,
    // we include it in our beforeEach function so that it runs before each test
    cy.visit('http://127.0.0.1:3000/people')
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

  it('verify people header section', () => {
    cy.get('div h1.leading-tight').should('have.text', 'People')
    cy.get("div p.text-2xl.text-gray-600").should('have.text', 'Our Team')
  })


  it('verify people list', () => {
    const peoples = [
      {
        "title": "Alex Kowen",
        "role": "Product Owner",
        "link": "/person/operations/alex-kowen"
      },
      {
        "title": "Amoli Ahuja",
        "role": "Financial Controller",
        "link": "/person/finance/amoli-ahuja"
      },
      {
        "title": "Anaisha Agarwal",
        "role": "Sales Executive",
        "link": "/person/sales/anaisha-agarwal"
      },
      {
        "title": "Arthur Mountbatten-Windsor",
        "role": "Manager",
        "link": "/person/management/arthur-mountbatten-windsor"
      },
      {
        "title": "Benicio Monserrate Rafael",
        "role": "Head of Operations",
        "link": "/person/operations/benicio-monserrate-rafael"
      },
      {
        "title": "Clare Harris",
        "role": "Office Manager",
        "link": "/person/management/clare-harris"
      },
      {
        "title": "Daniel William-Blake",
        "role": "Product Owner",
        "link": "/person/operations/daniel-william-blake"
      },
      {
        "title": "Gareth Jacobs",
        "role": "Sales Executive",
        "link": "/person/sales/gareth-jacobs"
      },
      {
        "title": "James Anderson",
        "role": "Finance Manager",
        "link": "/person/finance/james-anderson"
      },
      {
        "title": "Paul Smith",
        "role": "Sales Executive",
        "link": "/person/sales/paul-smith"
      },
      {
        "title": "Peter Withers",
        "role": "Logistics Manager",
        "link": "/person/operations/peter-withers"
      },
      {
        "title": "Robert Winters",
        "role": "Operations Manager",
        "link": "/person/operations/robert-winters"
      },
      {
        "title": "Sarah Jones",
        "role": "Head of Sales",
        "link": "/person/sales/sarah-jones"
      },
      {
        "title": "William Morris",
        "role": "IT Manager",
        "link": "/person/management/william-morris"
      }
    ];

    const element = cy.get("main div.container").find("article")
    element.should("have.length", peoples.length)
    element.each(($el, index, $list) => {
      // Left section of people list.
      cy.wrap($el).find("a").should("have.attr", "href", peoples[index].link)
      cy.wrap($el).get("a.block div.image__wrapper").find("img").should("have.attr", "alt", "Profile placeholder")
      // Right section of people list.
      cy.wrap($el).find("a").should("have.attr", "href", peoples[index].link)
      cy.wrap($el).find("h2").should("have.text", peoples[index].title)
      cy.wrap($el).find("p").should("have.text", peoples[index].role)
    });
  })

  it('verify page footer', () => {
    cy.get('footer nav .menu-item').each(($el, index, $list) => {
      cy.wrap($el).should("have.text", links[index])
    });
  })

})
