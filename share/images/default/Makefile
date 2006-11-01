.SUFFIXES: .svg

BATIK = batik-rasterizer
FILES = status-unknown-trig.svg status-unknown-untrig.svg status-good-trig.svg status-good-untrig.svg status-warning-trig.svg status-warning-untrig.svg status-critical-trig.svg status-critical-untrig.svg viewgraph-on.svg viewgraph-off.svg

all : $(FILES)
	@$(BATIK) -h 15 applytemplate.svg
	@$(BATIK) -h 15 edit.svg
	@$(BATIK) -h 15 delete.svg
	@$(BATIK) -h 15 disk.svg
	@$(BATIK) -h 15 duplicate.svg
	@$(BATIK) -h 15 recachedisk.svg
	@$(BATIK) -h 15 recacheinterface.svg
	@$(BATIK) -h 15 recacheproperties.svg
	@$(BATIK) -h 15 parameters.svg
	@$(BATIK) -h 15 slideshow.svg
	@$(BATIK) -h 15 view.svg
	@$(BATIK) -h 15 viewdisk.svg
	@$(BATIK) -h 15 viewinterface.svg
	@$(BATIK) -h 15 viewgraph-on.svg
	@$(BATIK) -h 15 viewgraph-off.svg
	@$(BATIK) -h 35 -d netmrg-logo-small.png netmrg-logo.svg
	@$(BATIK) -h 100 -d netmrg-logo-medium.png netmrg-logo.svg
	@$(BATIK) -h 250 -d netmrg-logo-large.png netmrg-logo.svg

arrow:
	@cp arrow.svg arrow-up.svg
	@$(BATIK) -h 15 arrow-up.svg
	@convert -rotate 90 arrow-up.png arrow-right.png
	@convert -rotate 180 arrow-up.png arrow-down.png
	@convert -rotate 270 arrow-up.png arrow-left.png
	@cat arrow-up.svg | perl -ne 's/#000000/#808080/; s/#333333/#A0A0A0/; print;' > arrow-up-disabled.svg
	@$(BATIK) -h 15 arrow-up-disabled.svg
	@convert -rotate 90 arrow-up-disabled.png arrow-right-disabled.png
	@convert -rotate 180 arrow-up-disabled.png arrow-down-disabled.png
	@convert -rotate 270 arrow-up-disabled.png arrow-left-disabled.png

arrow_limit:
	@cp arrow_limit.svg arrow_limit-up.svg
	@$(BATIK) -h 15 arrow_limit-up.svg
	@convert -rotate 90 arrow_limit-up.png arrow_limit-right.png
	@convert -rotate 180 arrow_limit-up.png arrow_limit-down.png
	@convert -rotate 270 arrow_limit-up.png arrow_limit-left.png
	@cat arrow_limit-up.svg | perl -ne 's/#000000/#808080/; s/#333333/#A0A0A0/; print;' > arrow_limit-up-disabled.svg
	@$(BATIK) -h 15 arrow_limit-up-disabled.svg
	@convert -rotate 90 arrow_limit-up-disabled.png arrow_limit-right-disabled.png
	@convert -rotate 180 arrow_limit-up-disabled.png arrow_limit-down-disabled.png
	@convert -rotate 270 arrow_limit-up-disabled.png arrow_limit-left-disabled.png

status:
	@$(MAKE) -s createstatus C1="#ff0000" C2="#ff4c4c" NAME="status-critical-trig.svg"
	@$(MAKE) -s createstatus C1="#ff0000" C2="#7f4c4c" NAME="status-critical-untrig.svg"
	@$(MAKE) -s createstatus C1="#009000" C2="#009000" NAME="status-good-trig.svg"
	@$(MAKE) -s createstatus C1="#009000" C2="#002000" NAME="status-good-untrig.svg"
	@$(MAKE) -s createstatus C1="#0000bf" C2="#4c4cc0" NAME="status-unknown-trig.svg"
	@$(MAKE) -s createstatus C1="#0000bf" C2="#4c4c7f" NAME="status-unknown-untrig.svg"
	@$(MAKE) -s createstatus C1="#e6e600" C2="#e6e500" NAME="status-warning-trig.svg"
	@$(MAKE) -s createstatus C1="#e6e600" C2="#999900" NAME="status-warning-untrig.svg"

viewgraph:
	@cp -f viewgraph.svg viewgraph-on.svg
	@cat viewgraph.svg | perl -ne 's/stroke:#e67f00;/stroke:#606060;/; s/fill:#e67f00;/fill:#606060;/; print;' > viewgraph-off.svg

createstatus:
	@cat status.svg | perl -ne 's/#7f7f7f/$(C1)/; s/#4c4c4c/$(C2)/; print;' > $(NAME)

%.svg: status viewgraph arrow
	@$(BATIK) -h 15 $@

clean:
	rm -f $(FILES)

