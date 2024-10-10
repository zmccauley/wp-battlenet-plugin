    const hoverTargets = document.querySelectorAll('.hover-target');
      const hoverText = document.getElementById('hoverText');

      hoverTargets.forEach(target => {
          target.addEventListener('mouseenter', () => {
              const affixId = target.getAttribute('data-id'); // Get the affix ID

              // Make AJAX call to get affix description
              fetch(my_ajax_object.ajax_url + '?action=my_affix_description&affix_id=' + affixId)
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          hoverText.textContent = data.data.description;
                          hoverText.style.display = 'block';

                          const rect = target.getBoundingClientRect();
                          hoverText.style.top = (rect.bottom + window.scrollY) + 'px';
                          hoverText.style.left = rect.left + 'px';
                      } else {
                          console.error('Error:', data.data);
                      }
                  })
                  .catch(error => console.error('Fetch error:', error));
          });

          target.addEventListener('mouseleave', () => {
              hoverText.style.display = 'none';
          });
      });