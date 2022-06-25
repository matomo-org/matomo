<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div class="container">

      <h1>Matomo UI demo</h1>
      <p>
        This page is intended to show all the UI components and styles available to use in Matomo.
      </p>

      <h2>Grid system</h2>
      <p>
        Materialize's grid system allows to organize the content in columns, all the while staying
        responsive for smaller screens.
      </p>
      <div class="grid-demo">
        <div class="row">
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
          <div class="col m1">.col m1</div>
        </div>
        <div class="row">
          <div class="col s8">.col m8</div>
          <div class="col m4">.col m4</div>
        </div>
        <div class="row">
          <div class="col m4">.col m4</div>
          <div class="col m4">.col m4</div>
          <div class="col m4">.col m4</div>
        </div>
        <div class="row">
          <div class="col m6">.col m6</div>
          <div class="col m6">.col m6</div>
        </div>
      </div>
      <p>
        Read more about <a href="http://materializecss.com/grid.html">Materialize's grid system</a>.
      </p>

      <h2>Typography</h2>

      <div class="demo">
        <h1>h1. heading</h1>
        <h2>h2. heading</h2>
        <h3>h3. heading</h3>
        <h4>h4. heading</h4>
        <h5>h5. heading</h5>
        <h6>h6. heading</h6>
        <p>
          This is a paragraph, and <a href="#">this is a link</a>.
        </p>
        <p>
          Another paragraph, lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
          eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
          quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
        </p>
      </div>

      <h3>Alignment classes</h3>

      <div class="demo">
        <p class="left">Left aligned block.</p>
        <p class="center">Center aligned block.</p>
        <p class="right">Right aligned block.</p>
      </div>
      <div class="demo-code">
        <pre>&lt;p class=&quot;left&quot;&gt;Left aligned block.&lt;/p&gt;
  &lt;p class=&quot;center&quot;&gt;Center aligned block.&lt;/p&gt;
  &lt;p class=&quot;right&quot;&gt;Right aligned block.&lt;/p&gt;</pre>
      </div>

      <div class="demo">
        <p class="left-align">Left aligned text.</p>
        <p class="center-align">Center aligned text.</p>
        <p class="right-align">Right aligned text.</p>
      </div>
      <div class="demo-code">
        <pre>&lt;p class=&quot;left-align&quot;&gt;Left aligned text.&lt;/p&gt;
  &lt;p class=&quot;center-align&quot;&gt;Center aligned text.&lt;/p&gt;
  &lt;p class=&quot;right-align&quot;&gt;Right aligned text.&lt;/p&gt;</pre>
      </div>

      <h3>Lists</h3>

      <div class="demo">
        <ul>
          <li>First item</li>
          <li>Second item</li>
        </ul>
      </div>
      <div class="demo-code">
        <pre>&lt;ul&gt;
    &lt;li&gt;...&lt;/li&gt;
  &lt;/ul&gt;</pre>
      </div>

      <h2>Buttons</h2>

      <div class="demo">
        <a class="btn" href="#">Link</a>
        <input type="button" class="btn" value="Input">
        <input type="submit" class="btn" value="Submit">
      </div>
      <div class="demo-code">
        <pre>&lt;a class=&quot;btn&quot; href=&quot;#&quot;&gt;Link&lt;/a&gt;
  &lt;input type=&quot;button&quot; class=&quot;btn&quot; value=&quot;Input&quot;&gt;
  &lt;input type=&quot;submit&quot; class=&quot;brn&quot; value=&quot;Submit&quot;&gt;</pre>
      </div>

      <div class="demo">
        <button class="btn btn-large">Large button</button>
        <a class="btn btn-large" href="#">Large button</a>
      </div>
      <div class="demo-code">
        <pre>&lt;button class=&quot;btn btn-large&quot;&gt;Large button&lt;/button&gt;
  &lt;a class=&quot;btn btn-large&quot; href=&quot;#&quot;&gt;Large button&lt;/a&gt;</pre>
      </div>

      <div class="demo">
        <div class="div-block">
          <a class="btn btn-block" href="#">Block button</a>
          <a class="btn btn-large btn-block" href="#">Large block button</a>
        </div>
      </div>
      <div class="demo-code">
        <pre>&lt;a class=&quot;btn btn-block&quot; href=&quot;#&quot;&gt;Block button&lt;/a&gt;
&lt;a class=&quot;btn btn-large btn-block&quot; href=&quot;#&quot;&gt;Large block button&lt;/a&gt;</pre>
      </div>

      <h3>Flat buttons</h3>

      <div class="demo">
        <a class="btn-flat" href="#">Flat button</a>
        <a class="btn-flat" href="#"><span class="icon-add"></span></a>
        <a class="btn-flat btn-large" href="#">Flat button</a>
        <a class="btn-flat btn-large" href="#"><span class="icon-add"></span></a>
      </div>
      <div class="demo-code">
              <pre>&lt;a class=&quot;btn-flat&quot; href=&quot;#&quot;&gt;Flat button&lt;/a&gt;
&lt;a class=&quot;btn-flat&quot; href=&quot;#&quot;&gt;
  &lt;span class=&quot;icon-add&quot;&gt;&lt;/span&gt;
&lt;/a&gt;
&lt;a class=&quot;btn-flat btn-large&quot; href=&quot;#&quot;&gt;Flat button&lt;/a&gt;
&lt;a class=&quot;btn-flat btn-large&quot; href=&quot;#&quot;&gt;
  &lt;span class=&quot;icon-add&quot;&gt;&lt;/span&gt;
&lt;/a&gt;</pre>
      </div>

      <h2>Vue Components</h2>
      <p>The following are components you can use within your own Vue components. Note: they
      will not work directly in HTML; they MUST be used within a Vue component.</p>

      <DemoCodePair
        v-for="snippet in demoSnippets"
        :key="snippet.id"
        :snippet="snippet"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import DemoCodePair from './DemoCodePair.vue';

export default defineComponent({
  props: {
    demoSnippets: {
      type: Array,
      required: true,
    },
  },
  components: {
    DemoCodePair,
  },
  created() {
    // TODO;
    /*
    $(function () {
      var icons = {
        'Manage': [
          'add',
          'edit',
          'delete',
          'plus',
          'minus',
          'archive'
        ],
        'Alerts': [
          'error',
          'warning',
          'info',
          'info2',
          'success',
          'help',
          'help-alt',
          'ok'
        ],
        'Navigation': [
          'arrow-left',
          'arrow-right',
          'arrow-top',
          'arrow-bottom',
          'arrow-collapsed',
          'zoom-in',
          'zoom-out',
          'show',
          'hide',
          'search',
          'menu-hamburger',
          'more-horiz',
          'more-verti',
          'arrowup',
          'arrowdown'
        ],
        'Window-Widget': [
          'minimise',
          'fullscreen',
          'close',
          'maximise',
          'newtab',
          'refresh',
          'reload'
        ],
        'Reports': [
          'table',
          'table-more',
          'chart-bar',
          'chart-pie',
          'chart-line',
          'chart-line-details',
          'evolution',
          'funnel',
          'form',
          'transition',
          'overlay',
          'lab',
          'clock'
        ],
        'Users': [
          'user',
          'user-add',
          'users',
          'alien'
        ],
        'Date-picker': [
          'calendar'
        ],
        'Annotations': [
          'annotation'
        ],
        'E-commerce': [
          'ecommerce-order',
          'ecommerce-abandoned-cart'
        ],
        'Goals': [
          'goal'
        ],
        'Insights': [
          'insights'
        ],
        'Segments': [
          'segment'
        ],
        'Visitors': [
          'visitor-profile',
          'segmented-visits-log'
        ],
        'Lock': [
          'locked',
          'locked-2',
          'locked-3',
          'locked-4'
        ],
        'Other': [
          'configure',
          'document',
          'email',
          'export',
          'feed',
          'download',
          'image',
          'code',
          'star',
          'drop',
          'business',
          'finance',
          'folder',
          'folder-charts',
          'github',
          'open-source',
          'puzzle',
          'server',
          'server-alt',
          'tag-cloud',
          'audio',
          'play',
          'pause',
          'replay',
          'stop',
          'fast-forward',
          'fast-rewind',
          'skip-next',
          'skip-previous',
          'sign-in',
          'sign-out',
          'settings',
          'rocket',
          'bug',
          'upload',
          'embed',
          'heart'
        ]
      };

      var iconsDiv = $('#icons');
      for(var category in icons) {
        if (icons.hasOwnProperty(category)) {
          iconsDiv.append('<h4>' + category + '</h4>');
          var row = $('<div class="row"></div>');
          icons[category].forEach(function(icon) {
            icon = 'icon-' + icon;
            row.append('<div class="col s4 icon"><span class="' + icon + '"></span> ' + icon + '</div>');
          });
          iconsDiv.append(row);
        }
      }
    });
    */
  },
});
</script>
