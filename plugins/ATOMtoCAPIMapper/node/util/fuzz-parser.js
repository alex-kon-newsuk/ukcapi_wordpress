var _ = require('lodash');
var slug = require('slug');
var cheerio = require('cheerio');
var crypto = require('crypto');

var PUB_ID = 'nypost';
var ARTICLE_PREFIX = 'NYP_';

function getGroupId(group) {
  return 'G_' + crypto.createHash('md5').update(group.article_ids.join('_')).digest('hex');
}

module.exports = {
  getCollections: function(fuzz, dateKey) {
    // Dynamically generate sections for the front & back covers
    if (fuzz.front_cover) {
      fuzz.sections.unshift({
        title: 'Front Cover',
        t3_article_ids: [fuzz.front_cover.article_id]
      });
    }

    if (fuzz.back_cover) {
      fuzz.sections.push({
        title: 'Back Cover',
        t3_article_ids: [fuzz.back_cover.article_id]
      });
    }

    return _.map(fuzz.sections, function(section) {
      var tags = _.map([
        ['itp', PUB_ID].join('-'),
        ['itp', PUB_ID, dateKey].join('-'),
        slug(section.title)
      ], function(tag) { return tag.toLowerCase(); });

      var result = {
        pubId: PUB_ID,
        name: section.title,
        tags: tags
      };

      // We use friendlyId to correlate Fuzz sections with Content API collections,
      // so it must remain consistent, even if the name of the section changes.
      // For covers, since they don't have section IDs, we'll use the Fuzz issue ID
      if (section.id) {
        result.friendlyId = slug(tags[0] + '-' + section.id);
      } else {
        // Unfortunately we have to use the section name here, but since only
        // covers should get here, their names shouldn't ever change so it should be fine
        result.friendlyId = slug(tags[1] + '-' + tags[2]);
      }

      result.contents = _.chain(section.t3_article_ids)
        .map(function(id) {
          var idString = id.toString();

          // Check if this article is part of a group
          var group = _.find(fuzz.groups, function(group) {
            return _.includes(group.article_ids, id);
          });
          if (group) {
            // If it's the first of a group, we replace this article id of that with the
            // expected group
            if (group.article_ids[0] === id) {
              idString = getGroupId(group);
            } else {
              // If it's one of the non-first articles, remove it from the collection since the
              // content gets rolled up into the grouped article
              return null;
            }
          }

          return {
            type: 'article',
            id: ARTICLE_PREFIX + idString
          };
        })
        .compact()
        .value();

      return result;
    });
  },

  getArticles: function(fuzz) {
    return _.chain(fuzz.articles)
      .map(function(article) {
        // Check if this is part of an article group so we can handle this appropriately
        var group = _.find(fuzz.groups, function(group) {
          return _.includes(group.article_ids, article.id);
        });

        // Article groups are rolled up into the first article of the group.
        // If this isn't the first article, we don't need to do anything.
        if (group && article.id !== group.article_ids[0]) {
          return null;
        }

        var prefix = ARTICLE_PREFIX;
        var id = article.id.toString();
        if (group) {
          id = getGroupId(group);
        }

        var result = {
          language: 'en-us',
          subsection: null,
          source: 'Fuzz',
          product: 'nypost',
          url: null,
          seoId: null,
          publishedAt: null,
          updatedAt: null,
          headline: group ? (group.name || article.title) : article.title,
          deck: null,
          id: prefix + id,
          summary: null,
          authors: null,
          isDeco: false
        };

        // Add placeholder headlines for covers if necessary
        if (!result.headline) {
          if (fuzz.front_cover && fuzz.front_cover.article_id === article.id) {
            result.headline = 'Front Cover';
          } else if (fuzz.back_cover && fuzz.back_cover.article_id === article.id) {
            result.headline = 'Back Cover';
          }
        }

        var section = _.find(fuzz.sections, function(section) {
          return _.includes(section.t3_article_ids, article.id);
        });

        if (section) {
          result.section = section.title;
        }

        if (group) {
          result.body = [];
          for (var i = 0; i < group.article_ids.length; i++) {
            var mini = _.findWhere(fuzz.articles, { id: group.article_ids[i] });
            if (mini.title) {
              result.body.push({
                tag: 'p',
                text: cheerio.load(mini.title).html(),
                '@': {
                  type: 'headline'
                }
              });
            }

            if (mini.authors && mini.authors.length > 0) {
              // Copied implementation from CloudPress
              var byline = 'By ';
              for (var j = 0; j < mini.authors.length; j++) {
                if (mini.authors.length > 1 && j === mini.authors.length - 1) {
                  byline += ' and ';
                }
                byline += '<span class="byline-author">' + mini.authors[j] + '</span>';
                if (j === mini.authors.length - 3) {
                  byline += ', ';
                }
              }
              result.body.push({
                tag: 'p',
                text: byline,
                '@': {
                  type: 'byline'
                }
              });
            }

            Array.prototype.push.apply(result.body, this.getArticleBody(mini));
          }
        } else {
          result.body = this.getArticleBody(article);
          result.authors = _.map(article.authors, function(author) {
            return {
              name: author
            };
          });
        }

        // Aggregate the hotspot info
        if (article.hotspot_ids && article.hotspot_ids.length > 0) {
          result.hotspots = [];
          _.each(article.hotspot_ids, function(hsId) {
            var hotspot = _.findWhere(fuzz.hotspots, { id: hsId });
            if (hotspot && hotspot.action_ids && hotspot.action_ids.length > 0) {
              // We only support one action per hotspot
              var action = _.findWhere(fuzz.actions, { id: hotspot.action_ids[0] });
              if (action && action.type === 'article_jump' && action.article_id) {
                result.hotspots.push({
                  type: 'article',
                  target: ARTICLE_PREFIX + action.article_id,
                  x1: hotspot.x1,
                  x2: hotspot.x2,
                  y1: hotspot.y1,
                  y2: hotspot.y2
                });
              }
            }
          });
        }

        return result;
      }, this)
      .reject(_.isNull)
      .value();
  },
  getArticleBody: function(article) {
    // Parse the article body
    var body = article.text ? _.map(article.text.split('\n\n'), function(text) {
      return {
        tag: 'p',
        text: cheerio.load(text).html()
      };
    }) : null;

    // Copy over the media items as body elements
    if (article.media && article.media.length > 0) {
      if (!body) {
        body = [];
      }
      _.each(article.media, function(m) {
        if (m.type === 'image') {
          body.push(this.getMediaElement(m));
        } else if (m.type === 'youtube') {
          body.push(this.getYouTubeElement(m));
        } else if (m.type === 'slideshow') {
          body.push(this.getSlideshowElement(m));
        }
      }, this);
    }
    return body;
  },
  getMediaElement: function(image) {
    return {
      tag: 'media',
      image: _.pick(image, 'width', 'height', 'src'),
      caption: image.caption,
      credit: image.credit
    };
  },
  getYouTubeElement: function(video) {
    return {
      tag: 'media',
      alts: !video.image ? [] : [
        _.pick(video.image, 'width', 'height', 'src'),
      ],
      caption: video.caption,
      image: {
        src: video.embed_url
      },
      '@': {
        type: 'video-external'
      }
    };
  },
  getSlideshowElement: function(slideshow) {
    return {
      tag: 'inset',
      body: _.map(slideshow.images, this.getMediaElement),
      '@': {
        type: 'slideshow'
      }
    };
  }
};
