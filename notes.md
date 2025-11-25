---
layout: base
title: Notes
description: A collection of recent posts on the topic of independent work.
toplevelsubnav_url: /notes
toplevelsubnav_text: Notes
---



<style>
    .notes { max-width: 900px; }
    .notes a { display: block; text-decoration:none; margin-bottom: 50px;  }
    .notes h3 { text-decoration: underline; margin-top: 0; }
    .notes .date { margin-bottom: 5px; }
</style>
<div class="container black">   
    <div class="inner">



        <div class="notes">
            {% for note in site.notes reversed %}
            <a href="{{note.url}}" class="note">
                <span class="date">{{note.date_posted}}</span>
                <h3>{{note.title}}</h3>
                <p>{{note.description}}</p>
            </a>
            {% endfor %}
        </div>

    </div>
</div>






<div class="container black" id="aboutus">   
    <div class="inner">
        <h2>The Independency Co.</h2>
        <p><strong>Creating confidence in freelancing.</strong></p>

        <p>Our work supports both freelancers and hirers to
        work better, together.</p>
        
        
        <p>Whether it’s our award winning mental health project
        Leapers, our free resources on freelancing.support, or
        our communities like Outside Perspective - we’ve
        been making freelancing work better for almost ten
        years.</p>

        <a href="/" class="button">Discover more</a>

        <div style="font-size: 12px;">
            The Independency Co. is a trading name of Foxlark Strategy Limited.<br/>
            a registered company in england and wales, no. 14421110<br/>
            VAT GB 296 6774 34.
        </div>
       
    </div>
</div>



