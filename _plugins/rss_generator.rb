require 'rss'
require 'open-uri'

module Jekyll
  class RssFeedGenerator < Generator
    safe true
    priority :high

    def generate(site)
      feed_url = site.config['substack_feed_url']
      return unless feed_url

      begin
        rss = RSS::Parser.parse(URI.open(feed_url))
        
        site.data['substack_items'] = rss.items.map do |item|
          {
            'title' => item.title.to_s,
            'link' => item.link.to_s,
            'description' => item.description.to_s,
            'pubDate' => item.pubDate
          }
        end
        
        Jekyll.logger.info "Substack Feed:", "Successfully loaded #{site.data['substack_items'].length} items"
      rescue => e
        Jekyll.logger.error "RSS Feed Error:", "Failed to fetch #{feed_url}: #{e.message}"
        site.data['substack_items'] = []
      end
    end
  end
end